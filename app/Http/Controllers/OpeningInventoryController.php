<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\AccountService;
use App\Services\StockService;
use Illuminate\Http\Request;

class OpeningInventoryController extends Controller
{
    use ShopScoped;

    public function __construct(
        protected StockService $stock,
        protected AccountService $accounts,
    ) {}

    public function index()
    {
        $this->stock->ensureDefaultLocations($this->shopId());

        $openedProductIds = StockMovement::where('shop_id', $this->shopId())
            ->where('document_type', 'opening_inventory')
            ->pluck('product_id')
            ->unique();

        $products = Product::where('shop_id', $this->shopId())
            ->whereNotIn('id', $openedProductIds)
            ->where('stock_quantity', 0)
            ->orderBy('name')
            ->get();

        $openedRecords = StockMovement::where('shop_id', $this->shopId())
            ->where('document_type', 'opening_inventory')
            ->with('product:id,name,stock_quantity')
            ->orderByDesc('created_at')
            ->get();

        return view('supply.opening-inventory.index', compact('products', 'openedRecords'));
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

                    $alreadyOpened = StockMovement::where('shop_id', $this->shopId())
                        ->where('product_id', $product->id)
                        ->where('document_type', 'opening_inventory')
                        ->exists();

                    if ($alreadyOpened) {
                        continue;
                    }

                    $quantity = (int) $row['quantity'];
                    if ($quantity === 0) {
                        continue;
                    }

                    $movement = $this->stock->setOpeningStock($product, $quantity);
                    $this->accounts->postOpeningInventory($movement);
                    $updated++;
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($updated === 0) {
            return redirect()->route('supply.opening-inventory.index')
                ->with('error', 'No opening stock was saved. Enter a quantity greater than 0 for new products.');
        }

        return redirect()->route('supply.opening-inventory.index')
            ->with('success', "Opening inventory saved. {$updated} product(s) set. Use Stock Adjustment for later changes.");
    }
}
