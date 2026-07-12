<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function dateRange(Request $request): array
    {
        if ($request->boolean('all_time')) {
            $shopId = auth()->user()->shop_id;
            $first = Order::where('shop_id', $shopId)->min('created_at');

            return [
                $first ? Carbon::parse($first)->startOfDay() : now()->startOfMonth(),
                now()->endOfDay(),
            ];
        }

        $start = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->subDays(29)->startOfDay();

        $end = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        return [$start, $end];
    }

    public function baseOrderQuery(int $shopId, Carbon $start, Carbon $end)
    {
        return Order::where('shop_id', $shopId)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'refunded');
    }

    public function revenue(int $shopId, Carbon $start, Carbon $end): float
    {
        return (float) $this->baseOrderQuery($shopId, $start, $end)->sum('total_amount');
    }

    public function orderCount(int $shopId, Carbon $start, Carbon $end): int
    {
        return $this->baseOrderQuery($shopId, $start, $end)->count();
    }

    public function posOrders(int $shopId, Carbon $start, Carbon $end)
    {
        return $this->baseOrderQuery($shopId, $start, $end)->whereNotNull('counter_id');
    }

    public function webOrders(int $shopId, Carbon $start, Carbon $end)
    {
        return $this->baseOrderQuery($shopId, $start, $end)->whereNull('counter_id');
    }

    public function costOfGoodsSold(int $shopId, Carbon $start, Carbon $end): float
    {
        return (float) OrderItem::query()
            ->whereHas('order', function ($q) use ($shopId, $start, $end) {
                $q->where('shop_id', $shopId)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', '!=', 'refunded');
            })
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('COALESCE(SUM(order_items.quantity * products.cost_price), 0) as cogs')
            ->value('cogs');
    }

    public function inventorySnapshot(int $shopId): array
    {
        $products = Product::where('shop_id', $shopId);

        return [
            'total_products' => (clone $products)->count(),
            'total_units' => (int) (clone $products)->sum('stock_quantity'),
            'cost_value' => (float) (clone $products)->selectRaw('SUM(cost_price * stock_quantity) as v')->value('v'),
            'retail_value' => (float) (clone $products)->selectRaw('SUM(selling_price * stock_quantity) as v')->value('v'),
            'low_stock' => (clone $products)->whereColumn('stock_quantity', '<=', 'alert_quantity')->count(),
            'out_of_stock' => (clone $products)->where('stock_quantity', '<=', 0)->count(),
        ];
    }

    public function dailyRevenueChart(int $shopId, Carbon $start, Carbon $end)
    {
        return $this->baseOrderQuery($shopId, $start, $end)
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(id) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get();
    }

    public function topSellingProducts(int $shopId, Carbon $start, Carbon $end, int $limit = 5)
    {
        return OrderItem::whereHas('order', function ($q) use ($shopId, $start, $end) {
                $q->where('shop_id', $shopId)
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', '!=', 'refunded');
            })
            ->select('product_id', DB::raw('SUM(quantity) as sold'))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('sold')
            ->limit($limit)
            ->get();
    }
}
