<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\StockMovement;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosController extends Controller
{
    public function __construct(protected AccountService $accounts) {}
    /**
     * Load the POS Terminal
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 🚨 SECURITY CHECK: Admins have full access. Staff MUST have a counter.
        if (!$user->counter_id && $user->role !== 'Shop Owner' && !$user->hasRole('Shop Owner')) {
            return redirect()->route('dashboard')->with('error', 'Access Denied: You must be assigned to a specific Counter before you can access the POS terminal. Please contact your Admin.');
        }

        $shopId = $user->shop_id;
        $categories = Category::where('shop_id', $shopId)->get();
        
        // Only show products that belong to this shop and are in stock
        $products = Product::where('shop_id', $shopId)
            ->where('stock_quantity', '>', 0)
            ->get();

        // 🚀 CATCH EXCHANGE PARAMETERS (If redirected from Sales Ledger)
        $exchangeOrder = $request->query('exchange_order');
        $returnProduct = $request->query('return_product');
        $returnQty = $request->query('return_qty');
        $credit = $request->query('credit', 0);

        return view('pos.index', compact('categories', 'products', 'exchangeOrder', 'returnProduct', 'returnQty', 'credit'));
    }

    /**
     * Process the sale, save customer, and record stock movements
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 🚨 BACKEND SECURITY CHECK: Admins bypass this.
        if (!$user->counter_id && $user->role !== 'Shop Owner' && !$user->hasRole('Shop Owner')) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction Blocked: No Counter assigned to your account.'
            ], 403);
        }

        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.qty' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'paid_amount' => 'required|numeric|min:0',
            'customer_phone' => 'nullable|string',
            'customer_name' => 'nullable|string',
        ]);

        $shopId = $user->shop_id;

        try {
            DB::beginTransaction();

            $totalAmount = 0;

            // 1. Calculate the exact total from the database
            foreach ($request->cart as $item) {
                $product = Product::where('shop_id', $shopId)->findOrFail($item['id']);
                if ($product->stock_quantity < $item['qty']) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }
                $totalAmount += $product->selling_price * $item['qty'];
            }

            // 🚀 EXCHANGE MATH & SECURITY
            $isExchange = $request->is_exchange ?? false;
            $exchangeCredit = $request->exchange_credit ?? 0;

            if ($isExchange && $totalAmount < $exchangeCredit) {
                throw new \Exception("Exchange Blocked: Cart total must equal or exceed the return credit. No cash refunds allowed.");
            }

            // Calculate what the customer actually owes after credit
            $payableAmount = max(0, $totalAmount - $exchangeCredit);

            // 2. Customer Handling
            $customerId = null;
            if (!empty($request->customer_phone)) {
                $customer = Customer::where('shop_id', $shopId)->where('phone', $request->customer_phone)->first();

                if ($customer) {
                    if (!empty($request->customer_name) && $customer->name !== $request->customer_name) {
                        $customer->update(['name' => $request->customer_name]);
                    }
                    $customerId = $customer->id;
                } else {
                    $newCustomer = Customer::create([
                        'shop_id' => $shopId,
                        'phone' => $request->customer_phone,
                        'name' => $request->customer_name ?? 'Guest User',
                    ]);
                    $customerId = $newCustomer->id;
                }
            }

            // 3. 🚀 FIX: Generate Invoice Number (SaaS Safe)
            $lastOrder = Order::where('shop_id', $shopId)->latest('id')->first();
            $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
            // Includes Shop ID in the prefix to prevent 1062 Duplicate Entry errors
            $invoiceNo = 'INV-' . $shopId . '-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // 4. Create the Main Order
            $order = Order::create([
                'shop_id' => $shopId,
                'user_id' => $user->id, 
                'counter_id' => $user->counter_id, // If Admin has no counter, this will safely be null
                'customer_id' => $customerId, 
                'invoice_no' => $invoiceNo,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => max(0, $request->paid_amount - $payableAmount),
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                
                // 🔒 FLAGS: Marks this as an exchange so it cannot be refunded
                'is_exchange_receipt' => $isExchange,
                'exchange_for_order_id' => $request->exchange_for_order_id,
                
                // 🚀 NEW FIELDS: Store exactly what was returned for the receipt
                'return_product_id' => $isExchange ? $request->return_product_id : null,
                'return_qty' => $isExchange ? $request->return_qty : null,
                'exchange_credit' => $isExchange ? $exchangeCredit : null,
            ]);

            // 5. Save Items & Decrease Inventory
            foreach ($request->cart as $item) {
                $product = Product::where('shop_id', $shopId)->findOrFail($item['id']);
                $subtotal = $product->selling_price * $item['qty'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                StockMovement::create([
                    'shop_id' => $shopId,
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'sale', 
                    'quantity' => $item['qty'],
                    'previous_stock' => $product->stock_quantity,
                    'current_stock' => $product->stock_quantity - $item['qty'],
                    'reference' => 'Sale - ' . $invoiceNo,
                ]);

                $product->decrement('stock_quantity', $item['qty']);
            }

            // 6. 🚀 RESTOCK THE RETURNED ITEM (If this is an exchange)
            if ($isExchange && $request->return_product_id) {
                $returnProduct = Product::where('shop_id', $shopId)->find($request->return_product_id);
                if ($returnProduct) {
                    $returnProduct->increment('stock_quantity', $request->return_qty);
                    
                    StockMovement::create([
                        'shop_id' => $shopId,
                        'product_id' => $returnProduct->id,
                        'user_id' => $user->id,
                        'type' => 'in',
                        'quantity' => $request->return_qty,
                        'previous_stock' => $returnProduct->stock_quantity - $request->return_qty,
                        'current_stock' => $returnProduct->stock_quantity,
                        'reference' => 'Exchange Return for ' . $invoiceNo,
                    ]);
                }
            }

            $order->load('items.product', 'counter');
            $this->accounts->postOrderSale($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment completed successfully!',
                'order_id' => $order->id,
                'change' => $order->change_amount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * View the printable receipt
     */
    public function receipt(Order $order)
    {
        if ($order->shop_id !== Auth::user()->shop_id) {
            abort(403, 'Unauthorized Access');
        }
        
        $order->load('items.product', 'user', 'customer');
        
        // 🚀 FETCH THE EXACT RETURNED PRODUCT FOR THE RECEIPT
        $returnProduct = null;
        if ($order->is_exchange_receipt && $order->return_product_id) {
            $returnProduct = Product::find($order->return_product_id);
        }

        return view('pos.receipt', compact('order', 'returnProduct'));
    }

    /**
     * Look up existing customer by phone number
     */
    public function lookupCustomer(Request $request)
    {
        $shopId = Auth::user()->shop_id;
        $phone = $request->phone;

        if (!$phone) {
            return response()->json(['found' => false]);
        }

        $customer = Customer::where('shop_id', $shopId)->where('phone', $phone)->first();

        if ($customer) {
            return response()->json([
                'found' => true,
                'name' => $customer->name
            ]);
        }
        return response()->json(['found' => false]);
    }

    /**
     * --- PWA FEATURE: Bulk Sync Offline Orders ---
     */
    public function syncOffline(Request $request)
    {
        $user = Auth::user();
        
        // 🚨 SECURITY CHECK: Admins bypass this.
        if (!$user->counter_id && $user->role !== 'Shop Owner' && !$user->hasRole('Shop Owner')) {
            return response()->json(['success' => false, 'message' => 'No counter assigned. Sync blocked.'], 403);
        }

        $shopId = $user->shop_id;
        $userId = $user->id;
        
        $orders = $request->input('orders', []);
        $syncedCount = 0;

        try {
            DB::beginTransaction();

            foreach ($orders as $offlineOrder) {
                // 1. Customer Handling 
                $customerId = null;
                if (!empty($offlineOrder['customer_phone'])) {
                    $customer = Customer::where('shop_id', $shopId)->where('phone', $offlineOrder['customer_phone'])->first();

                    if ($customer) {
                        if (!empty($offlineOrder['customer_name']) && $customer->name !== $offlineOrder['customer_name']) {
                            $customer->update(['name' => $offlineOrder['customer_name']]);
                        }
                        $customerId = $customer->id;
                    } else {
                        $newCustomer = Customer::create([
                            'shop_id' => $shopId,
                            'phone' => $offlineOrder['customer_phone'],
                            'name' => $offlineOrder['customer_name'] ?? 'Guest User',
                        ]);
                        $customerId = $newCustomer->id;
                    }
                }

                // 2. 🚀 FIX: Generate Invoice Number (SaaS Safe)
                $lastOrder = Order::where('shop_id', $shopId)->latest('id')->first();
                $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
                // Includes Shop ID in the prefix
                $invoiceNo = 'OFF-' . $shopId . '-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                // 3. Create Order
                $order = Order::create([
                    'shop_id' => $shopId,
                    'user_id' => $userId,
                    'counter_id' => $user->counter_id, // If Admin has no counter, this will safely be null
                    'customer_id' => $customerId,
                    'invoice_no' => $invoiceNo,
                    'total_amount' => $offlineOrder['total_amount'],
                    'paid_amount' => $offlineOrder['paid_amount'] ?? $offlineOrder['total_amount'],
                    'change_amount' => max(0, ($offlineOrder['paid_amount'] ?? $offlineOrder['total_amount']) - $offlineOrder['total_amount']),
                    'payment_method' => $offlineOrder['payment_method'],
                    'status' => 'completed',
                    'created_at' => Carbon::parse($offlineOrder['created_at'] ?? now()), 
                    'updated_at' => Carbon::parse($offlineOrder['created_at'] ?? now()),
                ]);

                // 4. Save Items and Deduct Stock
                foreach ($offlineOrder['items'] as $item) {
                    $product = Product::where('shop_id', $shopId)->find($item['id']);
                    
                    if ($product) {
                        $subtotal = $product->selling_price * $item['qty'];

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $item['qty'],
                            'unit_price' => $product->selling_price,
                            'subtotal' => $subtotal,
                        ]);

                        StockMovement::create([
                            'shop_id' => $shopId,
                            'product_id' => $product->id,
                            'user_id' => $userId,
                            'type' => 'sale',
                            'quantity' => $item['qty'],
                            'previous_stock' => $product->stock_quantity,
                            'current_stock' => $product->stock_quantity - $item['qty'],
                            'reference' => 'Offline Sync - ' . $invoiceNo,
                        ]);

                        $product->decrement('stock_quantity', $item['qty']);
                    }
                }
                $syncedCount++;

                $order->load('items.product', 'counter');
                $this->accounts->postOrderSale($order);
            }

            DB::commit();
            return response()->json(['success' => true, 'synced' => $syncedCount]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}