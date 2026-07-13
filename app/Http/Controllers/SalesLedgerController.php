<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\AccountService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesLedgerController extends Controller
{
    public function __construct(
        protected AccountService $accounts,
        protected StockService $stock,
    ) {}
    public function index(Request $request)
    {
        $shopId = Auth::user()->shop_id;
        
        $query = Order::where('shop_id', $shopId)->with(['customer', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('phone', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $refundedCount = (clone $query)->where('status', 'refunded')->count();
        $returnedCount = (clone $query)->where('status', 'returned')->count();
        $cancelledCount = (clone $query)->where('status', 'cancelled')->count();

        $orders = $query->latest()->paginate(15);
        $products = Product::where('shop_id', $shopId)->orderBy('name', 'asc')->get();

        return view('sales.index', compact('orders', 'products', 'refundedCount', 'returnedCount', 'cancelledCount'));
    }

    public function refund(Order $order)
    {
        if ($order->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized action.');
        }

        if (in_array($order->status, ['refunded', 'cancelled', 'returned'])) {
            return back()->with('error', 'This order has already been voided or refunded.');
        }

        if ($order->created_at < now()->subDays(7)) {
            return back()->with('error', 'The 7-day refund window has expired for this order.');
        }

        try {
            DB::beginTransaction();

            // 🚀 REVENUE WIPED DOWN TO 0
            $order->update([
                'status' => 'refunded',
                'paid_amount' => 0 
            ]);

            // 🚀 STOCK RESTORED UP (+)
            foreach ($order->items as $item) {
                $product = $item->product;

                if ($product) {
                    $this->stock->restockForDocument(
                        $product,
                        $item->quantity,
                        'Refund - ' . $order->invoice_no,
                        'order_refund',
                        $order->id,
                        'order_refund',
                        Auth::id(),
                    );
                }
            }

            $order->load('items.product', 'counter');
            $this->accounts->postOrderRefund($order);
            
            DB::commit();
            return back()->with('success', "Order {$order->invoice_no} has been refunded, stock restored, and revenue deducted.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process refund. ' . $e->getMessage());
        }
    }
}