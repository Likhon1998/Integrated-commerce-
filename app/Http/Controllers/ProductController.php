<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('shop_id', Auth::user()->shop_id)
            ->with(['category', 'brand'])
            ->latest()
            ->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('shop_id', Auth::user()->shop_id)->orderBy('name')->get();
        $brands = Brand::where('shop_id', Auth::user()->shop_id)->where('is_active', true)->orderBy('name')->get();

        return view('products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:products,barcode',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('image');
        $data['shop_id'] = Auth::user()->shop_id;
        $data = $this->applyBrandData($data);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product added successfully to inventory!');
    }

    public function edit(Product $product)
    {
        if ($product->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized access.');
        }

        $categories = Category::where('shop_id', Auth::user()->shop_id)->orderBy('name')->get();
        $brands = Brand::where('shop_id', Auth::user()->shop_id)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:products,barcode,' . $product->id,
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('image');
        $data = $this->applyBrandData($data);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        if ($product->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized access.');
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted from inventory!');
    }

    public function importForm()
    {
        return view('products.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $shopId = Auth::user()->shop_id;
        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $header = array_map('strtolower', array_map('trim', fgetcsv($handle) ?: []));

        $required = ['name', 'barcode', 'cost_price', 'selling_price', 'stock_quantity'];
        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                fclose($handle);
                return back()->withErrors(['csv_file' => "Missing required column: {$col}"]);
            }
        }

        $imported = 0;
        $skipped = 0;
        $row = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $row++;
            if (count(array_filter($line)) === 0) {
                continue;
            }

            $data = array_combine($header, array_pad($line, count($header), null));
            if (!$data || empty($data['name']) || empty($data['barcode'])) {
                $skipped++;
                continue;
            }

            if (Product::where('barcode', $data['barcode'])->exists()) {
                $skipped++;
                continue;
            }

            $categoryId = null;
            if (!empty($data['category'])) {
                $category = Category::firstOrCreate(
                    ['shop_id' => $shopId, 'slug' => \Illuminate\Support\Str::slug($data['category'])],
                    ['name' => $data['category']]
                );
                $categoryId = $category->id;
            }

            $brandId = null;
            $brandName = null;
            if (!empty($data['brand'])) {
                $brand = Brand::firstOrCreate(
                    ['shop_id' => $shopId, 'name' => trim($data['brand'])],
                    ['is_active' => true]
                );
                $brandId = $brand->id;
                $brandName = $brand->name;
            }

            Product::create([
                'shop_id' => $shopId,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'brand_name' => $brandName,
                'name' => trim($data['name']),
                'barcode' => trim($data['barcode']),
                'sku' => $data['sku'] ?? null,
                'cost_price' => (float) ($data['cost_price'] ?? 0),
                'selling_price' => (float) ($data['selling_price'] ?? 0),
                'stock_quantity' => (int) ($data['stock_quantity'] ?? 0),
                'alert_quantity' => (int) ($data['alert_quantity'] ?? 5),
                'is_published' => true,
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->route('products.index')->with('success', "CSV import complete: {$imported} added, {$skipped} skipped.");
    }

    public function barcodes(Request $request)
    {
        $query = Product::where('shop_id', Auth::user()->shop_id)->orderBy('name');

        if ($request->filled('product_ids')) {
            $ids = array_filter(explode(',', $request->product_ids));
            $query->whereIn('id', $ids);
        }

        $products = $query->get();

        return view('products.barcodes', compact('products'));
    }

    private function applyBrandData(array $data): array
    {
        if (!empty($data['brand_id'])) {
            $brand = Brand::where('shop_id', Auth::user()->shop_id)->find($data['brand_id']);
            $data['brand_name'] = $brand?->name;
        } else {
            $data['brand_id'] = null;
            $data['brand_name'] = null;
        }

        return $data;
    }
}
