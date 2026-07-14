<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\AccountService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        protected StockService $stock,
        protected AccountService $accounts,
    ) {}
    public function index()
    {
        $products = Product::where('shop_id', Auth::user()->shop_id)
            ->with(['category', 'brand'])
            ->latest()
            ->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create(Request $request)
    {
        $categories = Category::where('shop_id', Auth::user()->shop_id)->orderBy('name')->get();
        $brands = Brand::where('shop_id', Auth::user()->shop_id)->where('is_active', true)->orderBy('name')->get();
        $returnTo = $this->productReturnRoute($request->query('from'));

        return view('products.create', compact('categories', 'brands', 'returnTo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:products,barcode',
            'sku' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'original_price' => 'nullable|numeric|min:0',
            'short_description' => 'nullable|string|max:2000',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_published' => 'nullable|boolean',
            'is_new_arrival' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'return_to' => 'nullable|in:opening-inventory',
        ]);

        $data = $request->except(['image', 'image_2', 'image_3', 'stock_quantity', 'return_to', 'is_published', 'is_new_arrival', 'is_featured']);
        $data['shop_id'] = Auth::user()->shop_id;
        $data['stock_quantity'] = 0;
        $data['is_published'] = $request->boolean('is_published', true);
        $data['is_new_arrival'] = $request->boolean('is_new_arrival');
        $data['is_featured'] = $request->boolean('is_featured');
        $data = $this->applyBrandData($data);
        $data = array_merge($data, $this->storeProductImages($request));

        Product::create($data);

        if ($request->input('return_to') === 'opening-inventory') {
            return redirect()->route('supply.opening-inventory.index')->with(
                'success',
                'Product added. Enter the opening quantity below.'
            );
        }

        return redirect()->route('products.index')->with(
            'success',
            'Product added. Set stock quantities in Stock & Supply → Opening Inventory.'
        );
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
            'sku' => 'nullable|string|max:100',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'original_price' => 'nullable|numeric|min:0',
            'short_description' => 'nullable|string|max:2000',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_image' => 'nullable|boolean',
            'remove_image_2' => 'nullable|boolean',
            'remove_image_3' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_new_arrival' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = $request->except([
            'image', 'image_2', 'image_3', 'stock_quantity',
            'remove_image', 'remove_image_2', 'remove_image_3',
            'is_published', 'is_new_arrival', 'is_featured',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_new_arrival'] = $request->boolean('is_new_arrival');
        $data['is_featured'] = $request->boolean('is_featured');
        $data = $this->applyBrandData($data);
        $data = array_merge($data, $this->storeProductImages($request, $product));

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        if ($product->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized access.');
        }

        foreach ($product->imagePaths() as $path) {
            Storage::disk('public')->delete($path);
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

        $required = ['name', 'barcode', 'cost_price', 'selling_price'];
        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                fclose($handle);
                return back()->withErrors(['csv_file' => "Missing required column: {$col}"]);
            }
        }

        $imported = 0;
        $skipped = 0;
        $stockSet = 0;
        $row = 1;

        $this->stock->ensureDefaultLocations($shopId);

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

            $openingQty = (int) ($data['stock_quantity'] ?? 0);

            DB::transaction(function () use ($shopId, $categoryId, $brandId, $brandName, $data, $openingQty, &$imported, &$stockSet) {
                $product = Product::create([
                    'shop_id' => $shopId,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'brand_name' => $brandName,
                    'name' => trim($data['name']),
                    'barcode' => trim($data['barcode']),
                    'sku' => $data['sku'] ?? null,
                    'cost_price' => (float) ($data['cost_price'] ?? 0),
                    'selling_price' => (float) ($data['selling_price'] ?? 0),
                    'stock_quantity' => 0,
                    'alert_quantity' => (int) ($data['alert_quantity'] ?? 5),
                    'is_published' => true,
                ]);

                if ($openingQty > 0) {
                    $movement = $this->stock->setOpeningStock($product, $openingQty);
                    $this->accounts->postOpeningInventory($movement);
                    $stockSet++;
                }

                $imported++;
            });
        }

        fclose($handle);

        $message = "CSV import complete: {$imported} added, {$skipped} skipped.";
        if ($stockSet > 0) {
            $message .= " Opening stock recorded for {$stockSet} product(s).";
        }

        return redirect()->route('products.index')->with('success', $message);
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

    /**
     * Handle up to 3 product images (primary + gallery). Keys: image, image_2, image_3.
     */
    private function storeProductImages(Request $request, ?Product $existing = null): array
    {
        $out = [];
        $slots = ['image', 'image_2', 'image_3'];

        foreach ($slots as $slot) {
            $removeKey = 'remove_'.$slot;
            if ($existing && $request->boolean($removeKey) && $existing->{$slot}) {
                Storage::disk('public')->delete($existing->{$slot});
                $out[$slot] = null;
            }

            if ($request->hasFile($slot)) {
                if ($existing && $existing->{$slot}) {
                    Storage::disk('public')->delete($existing->{$slot});
                }
                $out[$slot] = $request->file($slot)->store('products', 'public');
            }
        }

        return $out;
    }

    private function productReturnRoute(?string $from): array
    {
        if ($from === 'opening-inventory') {
            return [
                'url' => route('supply.opening-inventory.index'),
                'key' => 'opening-inventory',
                'label' => 'Back to Opening Inventory',
            ];
        }

        return [
            'url' => route('products.index'),
            'key' => null,
            'label' => 'Back to Inventory',
        ];
    }
}
