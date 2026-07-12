<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    public function processExchange(Request $request, Order $order)
    {
        $request->validate([
            'return_product_id' => 'required|exists:products,id',
            'return_qty' => 'required|integer|min:1',
        ]);

        // Calculate the credit value of the returned item
        $returnProduct = Product::findOrFail($request->return_product_id);
        $creditValue = $returnProduct->selling_price * $request->return_qty;

        // Redirect directly to the POS Terminal with the Exchange Mode parameters!
        return redirect()->route('pos.index', [
            'exchange_order' => $order->id,
            'return_product' => $returnProduct->id,
            'return_qty' => $request->return_qty,
            'credit' => $creditValue
        ]);
    }
}