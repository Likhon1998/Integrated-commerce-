<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;

class OpeningInventoryController extends Controller
{
    use ShopScoped;

    public function __construct(protected StockService $stock) {}

    public function index()
    {
        $this->stock->ensureDefaultLocations($this->shopId());
        $products = Product::where('shop_id', $this->shopId())->orderBy('name')->get();
        return view('supply.opening-inventory.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        $updated = 0;
        try {
            $this->stock->transaction(function () use ($request, &$updated) {
                foreach ($request->items as $row) {
                    $product = Product::where('shop_id', $this->shopId())->findOrFail($row['product_id']);
                    if ((int) $row['quantity'] === (int) $product->stock_quantity) {
                        continue;
                    }
                    $this->stock->setOpeningStock($product, (int) $row['quantity']);
                    $updated++;
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.opening-inventory.index')
            ->with('success', "Opening inventory saved. {$updated} product(s) updated and synced to POS & web store.");
    }
}
