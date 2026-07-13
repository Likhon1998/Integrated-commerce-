<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Order;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesReturnController extends Controller
{
    use ShopScoped;

    public function __construct(protected StockService $stock) {}

    public function index()
    {
        $returns = SalesReturn::where('shop_id', $this->shopId())
            ->with('order')
            ->latest()
            ->paginate(15);

        return view('supply.sales-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $orders = Order::where('shop_id', $this->shopId())
            ->whereIn('status', ['completed', 'refunded'])
            ->with('items.product')
            ->latest()
            ->limit(50)
            ->get();

        $selectedOrder = $request->order_id
            ? Order::where('shop_id', $this->shopId())->with('items.product')->find($request->order_id)
            : null;

        return view('supply.sales-returns.create', compact('orders', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.refund_amount' => 'required|numeric|min:0',
        ]);

        $order = Order::where('shop_id', $this->shopId())->findOrFail($request->order_id);

        try {
            $this->stock->transaction(function () use ($request, $order) {
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

                foreach ($request->items as $item) {
                    if ((int) $item['quantity'] < 1) {
                        continue;
                    }
                    SalesReturnItem::create([
                        'sales_return_id' => $return->id,
                        'order_item_id' => $item['order_item_id'],
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'refund_amount' => $item['refund_amount'],
                    ]);
                    $product = Product::where('shop_id', $this->shopId())->findOrFail($item['product_id']);
                    $this->stock->apply(
                        $product,
                        'in',
                        (int) $item['quantity'],
                        'Sales return ' . $return->return_number . ' for ' . $order->invoice_no,
                        'sales_return',
                        Auth::id(),
                        'sales_return',
                        $return->id,
                    );
                    $totalRefund += $item['refund_amount'];
                }

                $return->update(['total_refund' => $totalRefund]);
                $order->update(['status' => 'returned']);
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.sales-returns.index')->with('success', 'Sales return processed. Stock restored to store & web.');
    }
}
