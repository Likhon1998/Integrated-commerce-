<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\AccountService;
use App\Services\StockService;
use App\Services\WebsiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    public function __construct(
        private WebsiteService $website,
        private AccountService $accounts,
        private StockService $stock,
    ) {}

    public function home()
    {
        return view('website.home', $this->website->homepageData());
    }

    public function shop(Request $request)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $query = $this->website->catalogQuery($shopId)->with(['category', 'brand']);

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)
                    ->orWhere('id', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand_name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filter === 'deals') {
            $query->whereNotNull('original_price')
                ->whereColumn('original_price', '>', 'selling_price');
        } elseif ($request->filter === 'new') {
            $query->where(function ($q) {
                $q->where('is_new_arrival', true)->orWhere('created_at', '>=', now()->subDays(30));
            })->latest();
        } elseif ($request->filter === 'bestsellers') {
            $query->orderByDesc('is_best_seller')->orderByDesc('review_count')->latest();
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('shop_id', $shopId)->orderBy('name')->get();

        return view('website.shop', array_merge($this->website->homepageData(), compact('products', 'categories')));
    }

    public function category(string $slug)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $category = Category::where('shop_id', $shopId)
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug);
            })
            ->firstOrFail();

        if (blank($category->slug)) {
            $category->save(); // triggers slug generation
        }

        $products = $this->website->catalogQuery($shopId)
            ->where('category_id', $category->id)
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

        $products = $this->website->catalogQuery($shopId)
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
        $published = $product->is_published !== false;
        abort_unless($shopId && $product->shop_id === $shopId && $product->stock_quantity > 0 && $published, 404);

        $related = $this->website->catalogQuery($shopId)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        $variantOptions = $this->website->productVariantOptions($product);

        $reviews = \App\Models\CmsReview::where('shop_id', $shopId)
            ->where('is_published', true)
            ->where(function ($q) use ($product) {
                $q->where('product_id', $product->id)->orWhereNull('product_id');
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return view('website.product', array_merge($this->website->homepageData(), compact('product', 'related', 'reviews', 'variantOptions')));
    }

    public function trackOrderForm()
    {
        return view('website.track-order', $this->website->homepageData());
    }

    public function page(string $slug)
    {
        $page = $this->website->publishedPage($slug);
        abort_unless($page, 404);

        return view('website.cms-page', array_merge($this->website->homepageData(), compact('page')));
    }

    public function blogs(Request $request)
    {
        $data = $this->website->blogPageData(
            $request->query('q'),
            $request->query('category')
        );

        return view('website.blogs', $data);
    }

    public function blog(string $slug)
    {
        $blog = $this->website->publishedBlog($slug);
        abort_unless($blog, 404);

        $blog->increment('views_count');
        $blog->refresh();

        $related = \App\Models\CmsBlog::where('shop_id', $blog->shop_id)
            ->published()
            ->where('id', '!=', $blog->id)
            ->when($blog->category_id, fn ($q) => $q->where('category_id', $blog->category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('website.blog-show', array_merge($this->website->homepageData(), [
            'blog' => $blog,
            'relatedPosts' => $related,
            'popularPosts' => $this->website->popularBlogs(4),
            'blogCategories' => $this->website->blogCategories(),
        ]));
    }

    public function subscribeNewsletter(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        \App\Models\CmsNewsletterSubscriber::firstOrCreate(
            ['shop_id' => $shopId, 'email' => strtolower(trim($request->email))]
        );

        return back()->with('newsletter_success', 'Thanks for subscribing!');
    }

    public function faqs(Request $request)
    {
        return view('website.faqs', $this->website->faqPageData(
            $request->query('q'),
            $request->query('category')
        ));
    }

    public function contact()
    {
        return view('website.contact', $this->website->contactPageData());
    }

    public function submitContact(Request $request)
    {
        $shopId = $this->website->shopId();
        abort_unless($shopId, 404);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:200',
            'order_number' => 'nullable|string|max:80',
            'message' => 'required|string|max:5000',
        ]);

        \App\Models\CmsContactMessage::create([
            'shop_id' => $shopId,
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'order_number' => $data['order_number'] ?? null,
            'message' => $data['message'],
            'is_read' => false,
        ]);

        return back()->with('contact_success', 'Thanks! Your message has been sent. We\'ll get back to you soon.');
    }

    public function wishlist()
    {
        return view('website.wishlist', $this->website->homepageData());
    }

    public function compare()
    {
        return view('website.compare', $this->website->homepageData());
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

        foreach ($request->cart as $item) {
            $product = Product::where('shop_id', $shopId)->find($item['id']);
            if (! $product || $product->is_published === false) {
                return response()->json(['success' => false, 'message' => 'A product in your cart is no longer available.']);
            }
            if ($product->stock_quantity < $item['qty']) {
                return response()->json([
                    'success' => false,
                    'message' => "Not enough stock for {$product->name}. Only {$product->stock_quantity} left.",
                ]);
            }
        }

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

                $product = Product::where('shop_id', $shopId)->findOrFail($item['id']);

                $this->stock->recordSale(
                    $product,
                    (int) $item['qty'],
                    'Website order - ' . $order->invoice_no,
                    $fallbackUserId,
                    'order',
                    $order->id,
                );
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
