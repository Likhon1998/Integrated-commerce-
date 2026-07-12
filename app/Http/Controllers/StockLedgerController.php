<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $shopId = Auth::user()->shop_id;
        
        $products = Product::where('shop_id', $shopId)->orderBy('name')->get();
        $query = StockMovement::where('shop_id', $shopId)->with(['product', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $movements = $query->latest()->paginate(15)->appends($request->query());

        return view('stock.index', compact('products', 'movements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference' => 'required|string|max:255',
        ]);

        $shopId = Auth::user()->shop_id;
        $product = Product::where('shop_id', $shopId)->findOrFail($request->product_id);

        if ($request->type === 'out' && $request->quantity > $product->stock_quantity) {
            return redirect()->back()->with('error', "Cannot deduct {$request->quantity} items. You only have {$product->stock_quantity} left in stock!");
        }

        try {
            DB::transaction(function () use ($request, $product, $shopId) {
                $previousStock = $product->stock_quantity;
                
                $currentStock = $request->type === 'in' 
                    ? $previousStock + $request->quantity 
                    : $previousStock - $request->quantity;

                StockMovement::create([
                    'shop_id' => $shopId,
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => $request->type,
                    'quantity' => $request->quantity,
                    'previous_stock' => $previousStock,
                    'current_stock' => $currentStock,
                    'reference' => $request->reference,
                ]);

                $product->update(['stock_quantity' => $currentStock]);
            });

            return redirect()->route('stock.index')->with('success', 'Stock adjusted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to adjust stock. Please try again.');
        }
    }
}