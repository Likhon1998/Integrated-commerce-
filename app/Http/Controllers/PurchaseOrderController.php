<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    use ShopScoped;

    public function __construct(protected StockService $stock) {}

    public function index()
    {
        $orders = PurchaseOrder::where('shop_id', $this->shopId())
            ->with('supplier')
            ->latest()
            ->paginate(15);

        return view('supply.purchase-orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::where('shop_id', $this->shopId())->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('shop_id', $this->shopId())->orderBy('name')->get();
        return view('supply.purchase-orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $po = $this->stock->transaction(function () use ($request) {
            $total = 0;
            $po = PurchaseOrder::create([
                'shop_id' => $this->shopId(),
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'po_number' => $this->stock->generateNumber($this->shopId(), 'PO'),
                'status' => 'ordered',
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'notes' => $request->notes,
                'total_amount' => 0,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }

            $po->update(['total_amount' => $total]);
            return $po;
        });

        return redirect()->route('supply.purchase-orders.show', $po)->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeShop($purchaseOrder);
        $purchaseOrder->load(['supplier', 'items.product']);
        return view('supply.purchase-orders.show', ['order' => $purchaseOrder]);
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizeShop($purchaseOrder);
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.receive_qty' => 'required|integer|min:0',
        ]);

        try {
            $this->stock->transaction(function () use ($request, $purchaseOrder) {
                foreach ($request->items as $row) {
                    $item = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)->findOrFail($row['id']);
                    $receiveQty = (int) $row['receive_qty'];
                    if ($receiveQty < 1) {
                        continue;
                    }
                    $remaining = $item->quantity - $item->received_quantity;
                    if ($receiveQty > $remaining) {
                        throw new \InvalidArgumentException("Cannot receive more than ordered for {$item->product->name}.");
                    }
                    $product = Product::where('shop_id', $this->shopId())->findOrFail($item->product_id);
                    $this->stock->receivePurchaseItem($product, $receiveQty, $purchaseOrder->po_number, Auth::id(), $purchaseOrder->id);
                    $item->increment('received_quantity', $receiveQty);
                    $product->update(['cost_price' => $item->unit_cost]);
                }

                $purchaseOrder->load('items');
                $allReceived = $purchaseOrder->items->every(fn ($i) => $i->received_quantity >= $i->quantity);
                $anyReceived = $purchaseOrder->items->contains(fn ($i) => $i->received_quantity > 0);
                $purchaseOrder->update([
                    'status' => $allReceived ? 'received' : ($anyReceived ? 'partial' : $purchaseOrder->status),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock received and synced to POS & web store.');
    }
}
