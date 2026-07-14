<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\AccountService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalesReturnController extends Controller
{
    use ShopScoped;

    public function __construct(
        protected StockService $stock,
        protected AccountService $accounts,
    ) {}

    public function index()
    {
        $query = SalesReturn::where('shop_id', $this->shopId())
            ->with('order')
            ->latest();

        $user = Auth::user();
        if (! $user->isAdminUser() && $user->counter_id) {
            $query->whereHas('order', fn ($q) => $q->where('counter_id', $user->counter_id));
        }

        $returns = $query->paginate(15);

        return view('supply.sales-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $orders = Order::where('shop_id', $this->shopId())
            ->where('status', 'completed')
            ->with('items.product')
            ->latest()
            ->limit(50);

        $user = Auth::user();
        if (! $user->isAdminUser() && $user->counter_id) {
            $orders->where('counter_id', $user->counter_id);
        }

        $orders = $orders->get();

        $selectedOrder = null;
        $alreadyReturned = [];

        if ($request->order_id) {
            $selectedOrder = Order::where('shop_id', $this->shopId())
                ->with('items.product')
                ->find($request->order_id);

            if ($selectedOrder && ! Auth::user()->isAdminUser() && Auth::user()->counter_id
                && (int) $selectedOrder->counter_id !== (int) Auth::user()->counter_id) {
                $selectedOrder = null;
            }

            if ($selectedOrder) {
                $alreadyReturned = SalesReturnItem::whereHas('salesReturn', function ($q) use ($selectedOrder) {
                    $q->where('order_id', $selectedOrder->id)->where('status', 'completed');
                })
                    ->selectRaw('order_item_id, SUM(quantity) as returned_qty')
                    ->groupBy('order_item_id')
                    ->pluck('returned_qty', 'order_item_id')
                    ->all();
            }
        }

        return view('supply.sales-returns.create', compact('orders', 'selectedOrder', 'alreadyReturned'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => [
                'required',
                Rule::exists('orders', 'id')->where(fn ($q) => $q->where('shop_id', $this->shopId())),
            ],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.refund_amount' => 'required|numeric|min:0',
        ]);

        $order = Order::where('shop_id', $this->shopId())
            ->with('items.product')
            ->findOrFail($request->order_id);

        $user = Auth::user();
        if (! $user->isAdminUser() && $user->counter_id && (int) $order->counter_id !== (int) $user->counter_id) {
            abort(403, 'You can only process returns for sales from your counter.');
        }

        if ($order->status !== 'completed') {
            return back()->withInput()->with('error', 'Only completed orders can be processed as sales returns.');
        }

        $lines = collect($request->items)->filter(fn ($item) => (int) $item['quantity'] > 0)->values();

        if ($lines->isEmpty()) {
            return back()->withInput()->with('error', 'Enter a return quantity greater than 0 for at least one product.');
        }

        try {
            $this->stock->transaction(function () use ($request, $order, $lines) {
                $this->accounts->ensureShopAccounts($this->shopId());

                $totalRefund = 0;
                $return = SalesReturn::create([
                    'shop_id' => $this->shopId(),
                    'order_id' => $order->id,
                    'user_id' => Auth::id(),
                    'return_number' => $this->stock->generateNumber($this->shopId(), 'SR'),
                    'status' => 'completed',
                    'notes' => $request->notes,
                    'total_refund' => 0,
                ]);

                foreach ($lines as $item) {
                    $orderItem = OrderItem::where('order_id', $order->id)->findOrFail($item['order_item_id']);
                    $qty = (int) $item['quantity'];

                    $alreadyReturned = (int) SalesReturnItem::where('order_item_id', $orderItem->id)
                        ->whereHas('salesReturn', fn ($q) => $q->where('status', 'completed'))
                        ->sum('quantity');

                    $remaining = $orderItem->quantity - $alreadyReturned;
                    if ($qty > $remaining) {
                        throw new \InvalidArgumentException(
                            "Cannot return more than sold for " . ($orderItem->product->name ?? 'item') . ". Remaining: {$remaining}."
                        );
                    }

                    $refundAmount = (float) $item['refund_amount'];
                    if ($refundAmount <= 0) {
                        $refundAmount = round((float) $orderItem->unit_price * $qty, 2);
                    }

                    SalesReturnItem::create([
                        'sales_return_id' => $return->id,
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'quantity' => $qty,
                        'refund_amount' => $refundAmount,
                    ]);

                    $product = Product::where('shop_id', $this->shopId())->findOrFail($orderItem->product_id);

                    // Unique restock key per return line (avoids multi-product skip bug)
                    $this->stock->apply(
                        $product,
                        'in',
                        $qty,
                        'Sales return ' . $return->return_number . ' for ' . $order->invoice_no,
                        'sales_return',
                        Auth::id(),
                        'sales_return',
                        $return->id,
                    );

                    $totalRefund += $refundAmount;
                }

                $return->update(['total_refund' => $totalRefund]);
                $return->load(['items.product', 'order']);
                $this->accounts->postSalesReturn($return);

                // Mark order returned only when every line is fully returned
                $fullyReturned = $order->items->every(function ($orderItem) {
                    $returned = (int) SalesReturnItem::where('order_item_id', $orderItem->id)
                        ->whereHas('salesReturn', fn ($q) => $q->where('status', 'completed'))
                        ->sum('quantity');

                    return $returned >= $orderItem->quantity;
                });

                if ($fullyReturned) {
                    $order->update(['status' => 'returned']);
                }
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.sales-returns.index')
            ->with('success', 'Sales return processed. Stock restored and accounts updated.');
    }
}
