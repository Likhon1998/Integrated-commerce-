<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\AccountService;
use App\Services\WebsiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    public function __construct(
        private WebsiteService $website,
        private AccountService $accounts,
    ) {}

    public function home()
    {
        return view('website.home', $this->website->homepageData());
    }

    public function shop(Request $request)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $query = Product::where('shop_id', $shopId)->where('stock_quantity', '>', 0)->with('category');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand_name', 'like', "%{$search}%");
            });
        }

        if ($request->filter === 'deals') {
            $query->whereNotNull('original_price')
                ->whereColumn('original_price', '>', 'selling_price');
        } elseif ($request->filter === 'new') {
            $query->latest();
        } elseif ($request->filter === 'bestsellers') {
            $query->latest();
        }

        $products = $query->latest()->paginate(12);
        $categories = Category::where('shop_id', $shopId)->orderBy('name')->get();

        return view('website.shop', array_merge($this->website->homepageData(), compact('products', 'categories')));
    }

    public function category(string $slug)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $category = Category::where('shop_id', $shopId)->where('slug', $slug)->firstOrFail();
        $products = Product::where('shop_id', $shopId)
            ->where('category_id', $category->id)
            ->where('stock_quantity', '>', 0)
            ->with(['category', 'brand'])
            ->latest()
            ->paginate(12);

        return view('website.shop', array_merge($this->website->homepageData(), [
            'products' => $products,
            'categories' => Category::where('shop_id', $shopId)->orderBy('name')->get(),
            'activeCategory' => $category,
            'pageTitle' => $category->name,
            'pageSubtitle' => 'Browse all products in this category.',
        ]));
    }

    public function brand(string $slug)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $brand = Brand::where('shop_id', $shopId)
            ->where('is_active', true)
            ->get()
            ->first(fn ($b) => \Illuminate\Support\Str::slug($b->name) === $slug);

        abort_unless($brand, 404);

        $products = Product::where('shop_id', $shopId)
            ->where('stock_quantity', '>', 0)
            ->where(function ($q) use ($brand) {
                $q->where('brand_id', $brand->id)
                    ->orWhere('brand_name', $brand->name);
            })
            ->with(['category', 'brand'])
            ->latest()
            ->paginate(12);

        return view('website.shop', array_merge($this->website->homepageData(), [
            'products' => $products,
            'categories' => Category::where('shop_id', $shopId)->orderBy('name')->get(),
            'activeBrand' => $brand,
            'pageTitle' => $brand->name,
            'pageSubtitle' => 'Shop all products from ' . $brand->name . '.',
        ]));
    }

    public function product(Product $product)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId && $product->shop_id === $shopId && $product->stock_quantity > 0, 404);

        $related = Product::where('shop_id', $shopId)
            ->where('stock_quantity', '>', 0)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('website.product', array_merge($this->website->homepageData(), compact('product', 'related')));
    }

    public function trackOrderForm()
    {
        return view('website.track-order', $this->website->homepageData());
    }

    public function checkout(Request $request)
    {
        $shopId = $this->website->shopId();
        if (!$shopId || empty($request->cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty or store unavailable.']);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string',
        ]);

        $customer = Customer::firstOrCreate(
            ['phone' => $request->customer_phone, 'shop_id' => $shopId],
            ['name' => $request->customer_name, 'address' => $request->customer_address]
        );

        $deliveryFee = $request->delivery_fee ?? 0;
        $subtotal = collect($request->cart)->sum(fn ($item) => $item['price'] * $item['qty']);
        $finalTotal = $subtotal + $deliveryFee;

        $shopAdmin = \App\Models\User::where('shop_id', $shopId)->first();
        $fallbackUserId = $shopAdmin?->id ?? 1;

        try {
            DB::beginTransaction();

            $order = Order::create([
                'shop_id' => $shopId,
                'user_id' => $fallbackUserId,
                'invoice_no' => 'WEB-' . $shopId . '-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'total_amount' => $finalTotal,
                'delivery_charge' => $deliveryFee,
                'paid_amount' => 0,
                'payment_method' => 'cash_on_delivery',
                'status' => 'pending',
                'counter_id' => null,
            ]);

            foreach ($request->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                $product = Product::find($item['id']);
                if ($product) {
                    $previousStock = $product->stock_quantity;
                    $product->decrement('stock_quantity', $item['qty']);

                    StockMovement::create([
                        'shop_id' => $shopId,
                        'product_id' => $product->id,
                        'user_id' => $fallbackUserId,
                        'type' => 'sale',
                        'quantity' => $item['qty'],
                        'previous_stock' => $previousStock,
                        'current_stock' => $previousStock - $item['qty'],
                        'reference' => 'Website Order - ' . $order->invoice_no,
                    ]);
                }
            }

            $order->load('items.product');
            $this->accounts->postWebSale($order);

            DB::commit();

            return response()->json(['success' => true, 'invoice' => $order->invoice_no]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
        }
    }

    public function trackOrder(Request $request)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $request->validate([
            'invoice_no' => 'required|string',
            'phone' => 'required|string',
        ]);

        $order = Order::where('shop_id', $shopId)
            ->where('invoice_no', $request->invoice_no)
            ->whereHas('customer', fn ($q) => $q->where('phone', $request->phone))
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found. Please check your Invoice Number and Phone.',
            ]);
        }

        $statusMapping = [
            'pending' => 'Order Received & Pending',
            'processing' => 'Processing / Packing',
            'shipped' => 'Out for Delivery',
            'completed' => 'Delivered / Completed',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
            'refunded' => 'Refunded',
        ];

        return response()->json([
            'success' => true,
            'status' => $statusMapping[$order->status] ?? ucfirst($order->status),
            'raw_status' => $order->status,
            'date' => $order->created_at->format('d M Y, h:i A'),
            'total' => number_format($order->total_amount, 2),
        ]);
    }
}
