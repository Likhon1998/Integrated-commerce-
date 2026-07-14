<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ShopScoped;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Services\AccountService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseReturnController extends Controller
{
    use ShopScoped;

    public function __construct(
        protected StockService $stock,
        protected AccountService $accounts,
    ) {}

    public function index()
    {
        $returns = PurchaseReturn::where('shop_id', $this->shopId())
            ->with('supplier')
            ->latest()
            ->paginate(15);

        return view('supply.purchase-returns.index', compact('returns'));
    }

    public function create()
    {
        $suppliers = Supplier::where('shop_id', $this->shopId())->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('shop_id', $this->shopId())->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::where('shop_id', $this->shopId())
            ->whereIn('status', ['partial', 'received'])
            ->with('supplier')
            ->latest()
            ->get();

        return view('supply.purchase-returns.create', compact('suppliers', 'products', 'purchaseOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn ($q) => $q->where('shop_id', $this->shopId())),
            ],
            'purchase_order_id' => [
                'nullable',
                Rule::exists('purchase_orders', 'id')->where(fn ($q) => $q->where('shop_id', $this->shopId())),
            ],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('shop_id', $this->shopId())),
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $this->stock->transaction(function () use ($request) {
                $this->accounts->ensureShopAccounts($this->shopId());

                $total = 0;
                $return = PurchaseReturn::create([
                    'shop_id' => $this->shopId(),
                    'supplier_id' => $request->supplier_id,
                    'purchase_order_id' => $request->purchase_order_id,
                    'user_id' => Auth::id(),
                    'return_number' => $this->stock->generateNumber($this->shopId(), 'PR'),
                    'status' => 'completed',
                    'notes' => $request->notes,
                    'total_amount' => 0,
                ]);

                foreach ($request->items as $item) {
                    $subtotal = $item['quantity'] * $item['unit_cost'];
                    PurchaseReturnItem::create([
                        'purchase_return_id' => $return->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'subtotal' => $subtotal,
                    ]);

                    $product = Product::where('shop_id', $this->shopId())->findOrFail($item['product_id']);
                    $movement = $this->stock->apply(
                        $product,
                        'out',
                        (int) $item['quantity'],
                        'Purchase return ' . $return->return_number,
                        'purchase_return',
                        Auth::id(),
                        'purchase_return',
                        $return->id,
                    );

                    $this->accounts->postPurchaseReturn(
                        $movement,
                        (float) $item['unit_cost'],
                        $return->return_number,
                    );

                    $total += $subtotal;
                }

                $return->update(['total_amount' => $total]);
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.purchase-returns.index')
            ->with('success', 'Purchase return recorded. Stock and Accounts Payable updated.');
    }
}
