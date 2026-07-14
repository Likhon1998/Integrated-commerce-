<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop;
        $shopId = $user->shop_id;
        $isAdmin = $user->isAdminUser();
        $counterId = $user->counter_id;
        $counter = $counterId ? Counter::find($counterId) : null;

        app(StockService::class)->ensureDefaultLocations($shopId);

        $queryOrders = Order::where('shop_id', $shopId)
            ->whereIn('status', ['completed', 'pending', 'shipped']);

        if (! $isAdmin) {
            if ($counterId) {
                $queryOrders->where('counter_id', $counterId);
            } else {
                $queryOrders->whereRaw('1 = 0');
            }
        }

        $today = Carbon::today();
        $weekStart = $today->copy()->subDays(6)->startOfDay();
        $prevWeekStart = $today->copy()->subDays(13)->startOfDay();
        $prevWeekEnd = $today->copy()->subDays(7)->endOfDay();

        $todaySales = (float) (clone $queryOrders)->whereDate('created_at', $today)->sum('total_amount');
        $todayOrdersCount = (int) (clone $queryOrders)->whereDate('created_at', $today)->count();

        $weekSales = (float) (clone $queryOrders)->where('created_at', '>=', $weekStart)->sum('total_amount');
        $weekOrders = (int) (clone $queryOrders)->where('created_at', '>=', $weekStart)->count();
        $prevWeekSales = (float) (clone $queryOrders)
            ->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])
            ->sum('total_amount');
        $prevWeekOrders = (int) (clone $queryOrders)
            ->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])
            ->count();

        if ($isAdmin) {
            $totalCustomers = Customer::where('shop_id', $shopId)->count();
            $registeredCustomers = $totalCustomers;
        } else {
            $totalCustomers = (int) Order::where('shop_id', $shopId)
                ->when($counterId, fn ($q) => $q->where('counter_id', $counterId), fn ($q) => $q->whereRaw('1 = 0'))
                ->whereNotNull('customer_id')
                ->selectRaw('COUNT(DISTINCT customer_id) as aggregate')
                ->value('aggregate');
            $registeredCustomers = $totalCustomers;
        }

        $lowStockCount = Product::where('shop_id', $shopId)
            ->whereColumn('stock_quantity', '<=', 'alert_quantity')
            ->count();

        $totalProducts = Product::where('shop_id', $shopId)->count();

        $inventoryValue = (float) (Product::where('shop_id', $shopId)
            ->selectRaw('COALESCE(SUM(selling_price * stock_quantity), 0) as total_value')
            ->value('total_value') ?? 0);

        $salesChangePct = $this->pctChange($weekSales, $prevWeekSales);
        $ordersChangePct = $this->pctChange($weekOrders, $prevWeekOrders);

        $salesChartLabels = [];
        $salesChartThisWeek = [];
        $salesChartLastWeek = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $prevDay = $day->copy()->subDays(7);
            $salesChartLabels[] = $day->format('M j');
            $salesChartThisWeek[] = round((float) (clone $queryOrders)->whereDate('created_at', $day)->sum('total_amount'), 2);
            $salesChartLastWeek[] = round((float) (clone $queryOrders)->whereDate('created_at', $prevDay)->sum('total_amount'), 2);
        }

        $recentOrders = (clone $queryOrders)
            ->with(['customer'])
            ->latest()
            ->take(5)
            ->get();

        $scopedOrderFilter = function ($q) use ($shopId, $isAdmin, $counterId) {
            $q->where('shop_id', $shopId)
                ->whereIn('status', ['completed', 'pending', 'shipped']);
            if (! $isAdmin) {
                if ($counterId) {
                    $q->where('counter_id', $counterId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }
        };

        $topProducts = OrderItem::query()
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as sold_qty'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->whereHas('order', function ($q) use ($scopedOrderFilter, $weekStart) {
                $scopedOrderFilter($q);
                $q->where('created_at', '>=', $weekStart);
            })
            ->groupBy('order_items.product_id')
            ->orderByDesc('sold_qty')
            ->with('product')
            ->take(5)
            ->get();

        if ($topProducts->isEmpty()) {
            $topProducts = OrderItem::query()
                ->select(
                    'order_items.product_id',
                    DB::raw('SUM(order_items.quantity) as sold_qty'),
                    DB::raw('SUM(order_items.subtotal) as revenue')
                )
                ->whereHas('order', $scopedOrderFilter)
                ->groupBy('order_items.product_id')
                ->orderByDesc('sold_qty')
                ->with('product')
                ->take(5)
                ->get();
        }

        $categorySales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.shop_id', $shopId)
            ->whereIn('orders.status', ['completed', 'pending', 'shipped'])
            ->when(! $isAdmin, function ($q) use ($counterId) {
                if ($counterId) {
                    $q->where('orders.counter_id', $counterId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->where('orders.created_at', '>=', $weekStart)
            ->select(
                DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->groupBy('category_name')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        $categorySalesTotal = (float) $categorySales->sum('revenue');

        $pendingOnlineOrders = 0;
        if ($isAdmin) {
            $pendingOnlineOrders = (int) Order::where('shop_id', $shopId)
                ->whereNull('counter_id')
                ->where('status', 'pending')
                ->count();
        }

        $counterBreakdown = collect();
        $onlineToday = null;

        if ($isAdmin) {
            $rows = Order::where('shop_id', $shopId)
                ->whereDate('created_at', $today)
                ->whereIn('status', ['completed', 'pending', 'shipped'])
                ->select(
                    'counter_id',
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw('COALESCE(SUM(total_amount), 0) as sales_total'),
                    DB::raw('COUNT(DISTINCT customer_id) as customers_count')
                )
                ->groupBy('counter_id')
                ->get()
                ->keyBy('counter_id');

            $counterBreakdown = Counter::where('shop_id', $shopId)
                ->orderBy('name')
                ->get()
                ->map(function (Counter $c) use ($rows) {
                    $row = $rows->get($c->id);

                    return (object) [
                        'id' => $c->id,
                        'name' => $c->name,
                        'sales_total' => (float) ($row->sales_total ?? 0),
                        'orders_count' => (int) ($row->orders_count ?? 0),
                        'customers_count' => (int) ($row->customers_count ?? 0),
                    ];
                });

            $onlineRow = Order::where('shop_id', $shopId)
                ->whereNull('counter_id')
                ->whereDate('created_at', $today)
                ->whereIn('status', ['completed', 'pending', 'shipped'])
                ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total_amount), 0) as sales_total, COUNT(DISTINCT customer_id) as customers_count')
                ->first();

            $onlineToday = (object) [
                'name' => 'Online / no counter',
                'sales_total' => (float) ($onlineRow->sales_total ?? 0),
                'orders_count' => (int) ($onlineRow->orders_count ?? 0),
                'customers_count' => (int) ($onlineRow->customers_count ?? 0),
            ];
        }

        $dateRangeLabel = $weekStart->format('M j').' – '.$today->format('M j, Y');

        return view('dashboard', compact(
            'shop',
            'todaySales',
            'todayOrdersCount',
            'weekSales',
            'weekOrders',
            'salesChangePct',
            'ordersChangePct',
            'totalCustomers',
            'lowStockCount',
            'inventoryValue',
            'totalProducts',
            'isAdmin',
            'counter',
            'counterBreakdown',
            'onlineToday',
            'registeredCustomers',
            'salesChartLabels',
            'salesChartThisWeek',
            'salesChartLastWeek',
            'recentOrders',
            'topProducts',
            'categorySales',
            'categorySalesTotal',
            'pendingOnlineOrders',
            'dateRangeLabel',
        ));
    }

    private function pctChange(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
