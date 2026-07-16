<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\AccountService;
use App\Services\OnlineOrderTrackingService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnlineOrderController extends Controller
{
    public function __construct(
        protected AccountService $accounts,
        protected StockService $stock,
        protected OnlineOrderTrackingService $tracking,
    ) {}

    public function index(Request $request)
    {
        $shopId = Auth::user()->shop_id;

        $filterDate = $request->input('date');
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $ordersQuery = Order::where('shop_id', $shopId)
            ->whereNull('counter_id')
            ->with([
                'customer:id,name,phone,address',
                'items:id,order_id,product_id,quantity,subtotal',
                'items.product:id,name',
            ]);

        if ($filterDate) {
            $ordersQuery->whereDate('created_at', $filterDate);
        }

        if ($search) {
            $ordersQuery->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('shipping_tracking_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('phone', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $ordersQuery->where('status', $statusFilter);
        }

        $orders = $ordersQuery->latest('created_at')->paginate(15)->appends($request->query());

        session(['online_orders_seen_at' => now()->toDateTimeString()]);

        $statsQuery = Order::where('shop_id', $shopId)->whereNull('counter_id');
        if ($filterDate) {
            $statsQuery->whereDate('created_at', $filterDate);
        }

        $stats = $statsQuery->selectRaw("
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_count,
            COALESCE(SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END), 0) as processing_count,
            COALESCE(SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END), 0) as shipped_count,
            COALESCE(SUM(CASE WHEN status = 'shipped' AND payment_method = 'cash_on_delivery' THEN total_amount - COALESCE(delivery_charge, 0) ELSE 0 END), 0) as courier_receivables,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN paid_amount - COALESCE(delivery_charge, 0) ELSE 0 END), 0) as settled_revenue
        ")->first();

        $pendingCount = (int) ($stats->pending_count ?? 0);
        $processingCount = (int) ($stats->processing_count ?? 0);
        $shippedCount = (int) ($stats->shipped_count ?? 0);
        $courierReceivables = (float) ($stats->courier_receivables ?? 0);
        $settledRevenue = (float) ($stats->settled_revenue ?? 0);

        return view('online-orders.index', compact(
            'orders',
            'pendingCount',
            'processingCount',
            'shippedCount',
            'courierReceivables',
            'settledRevenue',
            'filterDate',
            'search',
            'statusFilter',
        ));
    }

    public function show(Order $order)
    {
        abort_unless($order->shop_id === Auth::user()->shop_id && $order->counter_id === null, 403);

        $order->load([
            'customer:id,name,phone,address,email',
            'items:id,order_id,product_id,quantity,unit_price,subtotal',
            'items.product:id,name',
            'statusLogs' => fn ($q) => $q->latest('id')->limit(20),
        ]);

        $timeline = $this->tracking->customerTimeline($order);
        $statusLabels = $this->tracking->statusLabels();

        return view('online-orders.show', compact('order', 'timeline', 'statusLabels'));
    }

    public function notifications()
    {
        $shopId = Auth::user()->shop_id;
        $seenAt = session('online_orders_seen_at');

        $orders = Order::where('shop_id', $shopId)
            ->whereNull('counter_id')
            ->with('customer:id,name,phone')
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'invoice_no', 'status', 'total_amount', 'customer_id', 'created_at']);

        $labels = $this->tracking->statusLabels();

        $items = $orders->map(function (Order $order) use ($seenAt, $labels) {
            $isNew = $order->status === 'pending'
                || ($seenAt ? $order->created_at->greaterThan($seenAt) : $order->created_at->greaterThan(now()->subDay()));

            return [
                'id' => $order->id,
                'invoice' => $order->invoice_no,
                'status' => $order->status,
                'status_label' => $labels[$order->status] ?? ucfirst($order->status),
                'customer' => $order->customer?->name ?? 'Guest',
                'phone' => $order->customer?->phone,
                'total' => number_format((float) $order->total_amount, 2),
                'at' => $order->created_at->diffForHumans(),
                'url' => route('online-orders.show', $order),
                'is_new' => $isNew,
            ];
        });

        return response()->json([
            'unread' => $items->where('is_new', true)->count(),
            'items' => $items->values(),
        ]);
    }

    public function markNotificationsSeen()
    {
        session(['online_orders_seen_at' => now()->toDateTimeString()]);

        return response()->json(['ok' => true]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->shop_id !== Auth::user()->shop_id || $order->counter_id !== null) {
            abort(403, 'Unauthorized Access');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled,returned,refunded',
            'customer_note' => 'nullable|string|max:500',
            'courier_name' => 'nullable|string|max:120',
            'tracking_number' => 'nullable|string|max:120',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if (
            $oldStatus === $newStatus
            && ! $request->filled('customer_note')
            && ! $request->filled('tracking_number')
            && ! $request->filled('courier_name')
        ) {
            return back();
        }

        if ($newStatus === 'shipped' && ! $request->filled('courier_name') && blank($order->shipping_courier)) {
            return back()->with('error', 'Please enter a courier / delivery partner name when marking as shipped.');
        }

        try {
            DB::beginTransaction();

            $paidAmount = $order->paid_amount;

            if ($newStatus === 'completed' && $order->payment_method === 'cash_on_delivery') {
                $paidAmount = $order->total_amount;
            }

            if (in_array($newStatus, ['cancelled', 'returned', 'refunded'])) {
                $paidAmount = 0;
            }

            $courierName = $request->filled('courier_name')
                ? $request->courier_name
                : $order->shipping_courier;
            $trackingNumber = $request->filled('tracking_number')
                ? $request->tracking_number
                : $order->shipping_tracking_no;

            $order->update([
                'status' => $newStatus,
                'paid_amount' => $paidAmount,
                'shipping_courier' => $courierName,
                'shipping_tracking_no' => $trackingNumber,
            ]);

            $defaultNotes = [
                'processing' => 'We are packing your items now.',
                'shipped' => 'Your package is on the way to your delivery address.',
                'completed' => 'Order delivered successfully.',
                'cancelled' => 'This order was cancelled.',
                'returned' => 'This order was returned to our store.',
                'refunded' => 'This order was refunded.',
            ];

            $this->tracking->upsertLatestLog(
                $order,
                $newStatus,
                $request->customer_note ?: ($defaultNotes[$newStatus] ?? null),
                $courierName,
                $trackingNumber,
                Auth::id(),
            );

            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $order->load('items.product');
                $this->accounts->postWebSettlement($order);
            }

            if (in_array($newStatus, ['cancelled', 'returned', 'refunded']) && ! in_array($oldStatus, ['cancelled', 'returned', 'refunded'])) {
                foreach ($order->items as $item) {
                    $product = $item->product;

                    if ($product) {
                        $this->stock->restockForDocument(
                            $product,
                            $item->quantity,
                            'Order '.ucfirst($newStatus).' - '.$order->invoice_no,
                            'order_refund',
                            $order->id,
                            'order_'.$newStatus,
                            Auth::id(),
                        );
                    }
                }

                $order->load('items.product', 'counter');
                $this->accounts->postOrderRefund($order);
            }

            DB::commit();

            return back()->with('success', "Order {$order->invoice_no} updated to ".ucfirst($newStatus).'. Customer can now see this on tracking.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }
}
