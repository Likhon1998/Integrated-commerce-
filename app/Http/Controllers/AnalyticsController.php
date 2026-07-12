<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function overview(Request $request)
    {
        [$start, $end] = $this->analytics->dateRange($request);
        $shopId = Auth::user()->shop_id;

        $revenue = $this->analytics->revenue($shopId, $start, $end);
        $expense = $this->analytics->costOfGoodsSold($shopId, $start, $end);
        $orders = $this->analytics->orderCount($shopId, $start, $end);
        $inventory = $this->analytics->inventorySnapshot($shopId);

        $posRevenue = (float) $this->analytics->posOrders($shopId, $start, $end)->sum('total_amount');
        $webRevenue = (float) $this->analytics->webOrders($shopId, $start, $end)->sum('total_amount');
        $posCount = $this->analytics->posOrders($shopId, $start, $end)->count();
        $webCount = $this->analytics->webOrders($shopId, $start, $end)->count();

        $chart = $this->analytics->dailyRevenueChart($shopId, $start, $end);
        $topProducts = $this->analytics->topSellingProducts($shopId, $start, $end);

        return view('analytics.overview', compact(
            'start', 'end', 'revenue', 'expense', 'orders', 'inventory',
            'posRevenue', 'webRevenue', 'posCount', 'webCount', 'chart', 'topProducts'
        ));
    }

    public function orders(Request $request)
    {
        [$start, $end] = $this->analytics->dateRange($request);
        $shopId = Auth::user()->shop_id;

        $posQuery = $this->analytics->posOrders($shopId, $start, $end);
        $webQuery = $this->analytics->webOrders($shopId, $start, $end);

        $summary = [
            'total' => $this->analytics->orderCount($shopId, $start, $end),
            'pos' => (clone $posQuery)->count(),
            'web' => (clone $webQuery)->count(),
            'pending' => $this->analytics->baseOrderQuery($shopId, $start, $end)->where('status', 'pending')->count(),
            'completed' => $this->analytics->baseOrderQuery($shopId, $start, $end)->where('status', 'completed')->count(),
        ];

        $recentOrders = Order::where('shop_id', $shopId)
            ->whereBetween('created_at', [$start, $end])
            ->with(['customer', 'counter'])
            ->latest()
            ->paginate(15)
            ->appends($request->all());

        $dailyOrders = $this->analytics->baseOrderQuery($shopId, $start, $end)
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(id) as total'),
                DB::raw('SUM(CASE WHEN counter_id IS NULL THEN 1 ELSE 0 END) as web_orders'),
                DB::raw('SUM(CASE WHEN counter_id IS NOT NULL THEN 1 ELSE 0 END) as pos_orders')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('day')
            ->get();

        return view('analytics.orders', compact('start', 'end', 'summary', 'recentOrders', 'dailyOrders'));
    }

    public function revenue(Request $request)
    {
        [$start, $end] = $this->analytics->dateRange($request);
        $shopId = Auth::user()->shop_id;

        $base = $this->analytics->baseOrderQuery($shopId, $start, $end);

        $summary = (clone $base)->select(
            DB::raw('COUNT(id) as total_orders'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'cash' THEN total_amount ELSE 0 END) as cash_total"),
            DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'card' THEN total_amount ELSE 0 END) as card_total"),
            DB::raw("SUM(CASE WHEN LOWER(payment_method) IN ('bkash','mobile') THEN total_amount ELSE 0 END) as mobile_total"),
            DB::raw('SUM(CASE WHEN counter_id IS NULL THEN total_amount ELSE 0 END) as web_revenue'),
            DB::raw('SUM(CASE WHEN counter_id IS NOT NULL THEN total_amount ELSE 0 END) as pos_revenue')
        )->first();

        $daily = (clone $base)->select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('COUNT(id) as orders'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('SUM(CASE WHEN counter_id IS NULL THEN total_amount ELSE 0 END) as web_revenue'),
            DB::raw('SUM(CASE WHEN counter_id IS NOT NULL THEN total_amount ELSE 0 END) as pos_revenue')
        )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('day')
            ->get();

        return view('analytics.revenue', compact('start', 'end', 'summary', 'daily'));
    }

    public function expense(Request $request)
    {
        [$start, $end] = $this->analytics->dateRange($request);
        $shopId = Auth::user()->shop_id;

        $cogs = $this->analytics->costOfGoodsSold($shopId, $start, $end);
        $revenue = $this->analytics->revenue($shopId, $start, $end);

        $productCosts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.shop_id', $shopId)
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'refunded')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('SUM(order_items.quantity * products.cost_price) as cost_total'),
                DB::raw('SUM(order_items.subtotal) as sales_total')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('cost_total')
            ->paginate(15)
            ->appends($request->all());

        return view('analytics.expense', compact('start', 'end', 'cogs', 'revenue', 'productCosts'));
    }

    public function inventory(Request $request)
    {
        $shopId = Auth::user()->shop_id;
        $inventory = $this->analytics->inventorySnapshot($shopId);

        [$start, $end] = $this->analytics->dateRange($request);
        $topSelling = $this->analytics->topSellingProducts($shopId, $start, $end, 10);

        $lowStock = Product::where('shop_id', $shopId)
            ->whereColumn('stock_quantity', '<=', 'alert_quantity')
            ->with('category')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        $categories = \App\Models\Category::where('shop_id', $shopId)
            ->withCount('products')
            ->get()
            ->map(function ($category) use ($shopId) {
                $stats = Product::where('shop_id', $shopId)
                    ->where('category_id', $category->id)
                    ->selectRaw('COUNT(*) as products, COALESCE(SUM(stock_quantity),0) as units, COALESCE(SUM(cost_price * stock_quantity),0) as cost_value')
                    ->first();

                return (object) [
                    'category' => $category,
                    'products' => $stats->products ?? 0,
                    'units' => $stats->units ?? 0,
                    'cost_value' => $stats->cost_value ?? 0,
                ];
            })
            ->sortByDesc('cost_value')
            ->values();

        return view('analytics.inventory', compact('start', 'end', 'inventory', 'topSelling', 'lowStock', 'categories'));
    }

    public function balance(Request $request)
    {
        [$start, $end] = $this->analytics->dateRange($request);
        $shopId = Auth::user()->shop_id;

        $revenue = $this->analytics->revenue($shopId, $start, $end);
        $expense = $this->analytics->costOfGoodsSold($shopId, $start, $end);
        $profit = $revenue - $expense;
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        $posRevenue = (float) $this->analytics->posOrders($shopId, $start, $end)->sum('total_amount');
        $webRevenue = (float) $this->analytics->webOrders($shopId, $start, $end)->sum('total_amount');

        $posCogs = (float) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.shop_id', $shopId)
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'refunded')
            ->whereNotNull('orders.counter_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price), 0) as cogs')
            ->value('cogs');

        $webCogs = (float) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.shop_id', $shopId)
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', '!=', 'refunded')
            ->whereNull('orders.counter_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price), 0) as cogs')
            ->value('cogs');

        $daily = $this->analytics->baseOrderQuery($shopId, $start, $end)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get();

        return view('analytics.balance', compact(
            'start', 'end', 'revenue', 'expense', 'profit', 'margin',
            'posRevenue', 'webRevenue', 'posCogs', 'webCogs', 'daily'
        ));
    }
}
