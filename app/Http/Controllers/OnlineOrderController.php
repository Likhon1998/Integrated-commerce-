<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\AccountService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnlineOrderController extends Controller
{
    public function __construct(
        protected AccountService $accounts,
        protected StockService $stock,
    ) {}
    public function index(Request $request)
    {
        $shopId = Auth::user()->shop_id;
        
        $filterDate = $request->input('date');
        $search = $request->input('search');

        $ordersQuery = Order::with(['customer', 'items.product'])->where('shop_id', $shopId)->whereNull('counter_id');
        $pendingQuery = Order::where('shop_id', $shopId)->whereNull('counter_id')->where('status', 'pending');
        $courierQuery = Order::where('shop_id', $shopId)->whereNull('counter_id')->where('status', 'shipped')->where('payment_method', 'cash_on_delivery');
        $settledQuery = Order::where('shop_id', $shopId)->whereNull('counter_id')->where('status', 'completed');

        if ($filterDate) {
            $ordersQuery->whereDate('created_at', $filterDate);
            $pendingQuery->whereDate('created_at', $filterDate);
            $courierQuery->whereDate('created_at', $filterDate);
            $settledQuery->whereDate('created_at', $filterDate);
        }

        if ($search) {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('phone', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());
        
        $pendingCount = $pendingQuery->count();
        
        // 🚀 ONLY COUNT THE ACTUAL PRODUCT PRICE (Total - Delivery Charge)
        $courierReceivables = $courierQuery->sum('total_amount') - $courierQuery->sum('delivery_charge');
        $settledRevenue = $settledQuery->sum('paid_amount') - $settledQuery->sum('delivery_charge');

        return view('online-orders.index', compact('orders', 'pendingCount', 'courierReceivables', 'settledRevenue', 'filterDate', 'search'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized Access');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled,returned,refunded'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return back();
        }

        try {
            DB::beginTransaction();

            $paidAmount = $order->paid_amount;
            
            // We set paid_amount to the full total here so the printed receipt correctly shows the customer paid in full. 
            // Our stat calculations above automatically filter out the delivery fee!
            if ($newStatus === 'completed' && $order->payment_method === 'cash_on_delivery') {
                $paidAmount = $order->total_amount;
            }
            
            if (in_array($newStatus, ['cancelled', 'returned', 'refunded'])) {
                $paidAmount = 0;
            }

            $order->update([
                'status' => $newStatus,
                'paid_amount' => $paidAmount,
            ]);

            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $order->load('items.product');
                $this->accounts->postWebSettlement($order);
            }

            if (in_array($newStatus, ['cancelled', 'returned', 'refunded']) && !in_array($oldStatus, ['cancelled', 'returned', 'refunded'])) {
                foreach ($order->items as $item) {
                    $product = $item->product;

                    if ($product) {
                        $this->stock->restockForDocument(
                            $product,
                            $item->quantity,
                            'Order ' . ucfirst($newStatus) . ' - ' . $order->invoice_no,
                            'order_refund',
                            $order->id,
                            'order_' . $newStatus,
                            Auth::id(),
                        );
                    }
                }

                $order->load('items.product', 'counter');
                $this->accounts->postOrderRefund($order);
            }

            DB::commit();
            return back()->with('success', "Order {$order->invoice_no} is now marked as " . ucfirst($newStatus) . "!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}