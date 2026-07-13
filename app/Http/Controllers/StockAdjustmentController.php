<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    use ShopScoped;

    public function __construct(protected StockService $stock) {}

    public function index(Request $request)
    {
        $products = Product::where('shop_id', $this->shopId())->orderBy('name')->get();
        $query = StockMovement::where('shop_id', $this->shopId())
            ->where('type', '!=', 'sale')
            ->with(['product', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $movements = $query->latest()->paginate(15)->appends($request->query());

        return view('supply.adjustments.index', compact('products', 'movements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference' => 'required|string|max:255',
        ]);

        $product = Product::where('shop_id', $this->shopId())->findOrFail($request->product_id);

        try {
            $this->stock->apply(
                $product,
                $request->type,
                (int) $request->quantity,
                $request->reference,
                'adjustment',
                Auth::id(),
                'stock_adjustment',
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.adjustments.index')->with('success', 'Stock adjustment saved and synced to POS & web store.');
    }
}
