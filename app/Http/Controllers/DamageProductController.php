<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DamageProductController extends Controller
{
    use ShopScoped;

    public function __construct(protected StockService $stock) {}

    public function index(Request $request)
    {
        $products = Product::where('shop_id', $this->shopId())->orderBy('name')->get();
        $damages = \App\Models\StockMovement::where('shop_id', $this->shopId())
            ->where('reason', 'damage')
            ->with(['product', 'user'])
            ->latest()
            ->paginate(15);

        $todayDamagedQty = (int) \App\Models\StockMovement::where('shop_id', $this->shopId())
            ->where('reason', 'damage')
            ->whereDate('created_at', now()->toDateString())
            ->sum('quantity');

        return view('supply.damage-products.index', compact('products', 'damages', 'todayDamagedQty'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reference' => 'required|string|max:255',
        ]);

        $product = Product::where('shop_id', $this->shopId())->findOrFail($request->product_id);

        try {
            $this->stock->apply(
                $product,
                'out',
                (int) $request->quantity,
                $request->reference,
                'damage',
                Auth::id(),
                'damage_product',
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.damage-products.index')->with('success', 'Damaged stock written off and synced to web store.');
    }
}
