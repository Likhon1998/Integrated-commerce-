<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (! Auth::user()?->isAdminUser()) {
            abort(403, 'Reports are only available to shop admins.');
        }
    }

    protected function completedOrdersQuery(int $shopId, $startDate, $endDate)
    {
        return Order::where('shop_id', $shopId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->where('is_exchange_receipt', false)
                    ->orWhereNull('is_exchange_receipt');
            });
    }

    public function dailySales(Request $request)
    {
        $this->ensureAdmin();

        $shopId = Auth::user()->shop_id;
        
        // 🚀 CHECK IF USER CLICKED "ALL TIME"
        if ($request->has('all_time')) {
            // Fetch the date of their very first order ever
            $firstOrder = Order::where('shop_id', $shopId)->min('created_at');
            $startDate = $firstOrder ? Carbon::parse($firstOrder)->startOfDay() : now()->startOfMonth();
            $endDate = now()->endOfDay();
        } else {
            // Otherwise, use the selected dates (or default to Today)
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::today()->startOfDay();
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::today()->endOfDay();
        }

        $netExpr = 'GREATEST(total_amount - COALESCE(discount_amount, 0) - COALESCE(exchange_credit, 0), 0)';

        // 1. PERIOD SNAPSHOT (Overview of Cash/Card/bKash for the selected date range)
        $summary = $this->completedOrdersQuery($shopId, $startDate, $endDate)
            ->select(
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("SUM({$netExpr}) as total_revenue"),
                DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'cash' THEN {$netExpr} ELSE 0 END) as cash_total"),
                DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'card' THEN {$netExpr} ELSE 0 END) as card_total"),
                DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'bkash' THEN {$netExpr} ELSE 0 END) as bkash_total")
            )->first();

        // 2. SALES BY EMPLOYEE (For the selected date range)
        $employeeSales = $this->completedOrdersQuery($shopId, $startDate, $endDate)
            ->select('user_id', DB::raw('COUNT(id) as total_orders'), DB::raw("SUM({$netExpr}) as total_revenue"))
            ->groupBy('user_id')
            ->with('user') 
            ->orderByDesc('total_revenue')
            ->get();

        // 3. SALES BY COUNTER (For the selected date range)
        $counterSales = $this->completedOrdersQuery($shopId, $startDate, $endDate)
            ->select('counter_id', DB::raw('COUNT(id) as total_orders'), DB::raw("SUM({$netExpr}) as total_revenue"))
            ->groupBy('counter_id')
            ->with('counter') 
            ->orderByDesc('total_revenue')
            ->get();

        // 4. HISTORICAL DAILY LEDGER (Filtered by the date range)
        $historicalSales = $this->completedOrdersQuery($shopId, $startDate, $endDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("SUM({$netExpr}) as total_revenue"),
                DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'cash' THEN {$netExpr} ELSE 0 END) as cash_total"),
                DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'card' THEN {$netExpr} ELSE 0 END) as card_total"),
                DB::raw("SUM(CASE WHEN LOWER(payment_method) = 'bkash' THEN {$netExpr} ELSE 0 END) as bkash_total")
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        return view('reports.daily', compact('summary', 'employeeSales', 'counterSales', 'historicalSales', 'startDate', 'endDate'));
    }

    public function bestSellers(Request $request)
    {
        $this->ensureAdmin();

        $shopId = Auth::user()->shop_id;
        
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::today()->subDays(30)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::today()->endOfDay();

        $bestSellers = \App\Models\OrderItem::whereHas('order', function ($query) use ($shopId, $startDate, $endDate) {
                $query->where('shop_id', $shopId)
                      ->whereBetween('created_at', [$startDate, $endDate])
                      ->where('status', 'completed')
                      ->where(function ($q) {
                          $q->where('is_exchange_receipt', false)
                              ->orWhereNull('is_exchange_receipt');
                      });
            })
            ->select(
                'product_id', 
                DB::raw('SUM(quantity) as total_sold')
            )
            ->groupBy('product_id')
            ->with('product') 
            ->orderByDesc('total_sold')
            ->paginate(15);

        $bestSellers->appends($request->all());

        return view('reports.best-sellers', compact('bestSellers', 'startDate', 'endDate'));
    }

    public function lowStock()
    {
        $shopId = Auth::user()->shop_id;
        
        // Note: No date range here, because Low Stock represents the physical items on shelves RIGHT NOW.
        $lowStockItems = \App\Models\Product::where('shop_id', $shopId)
            ->whereColumn('stock_quantity', '<=', 'alert_quantity') 
            ->with('category') 
            ->orderBy('stock_quantity', 'asc') 
            ->paginate(15);

        return view('reports.low-stock', compact('lowStockItems'));
    }

    public function staffPerformance(Request $request)
    {
        $this->ensureAdmin();

        $shopId = Auth::user()->shop_id;
        $view = in_array($request->input('view'), ['person', 'counter', 'log'], true)
            ? $request->input('view')
            : 'person';

        if ($request->boolean('all_time')) {
            $firstOrder = Order::where('shop_id', $shopId)->min('created_at');
            $startDate = $firstOrder ? Carbon::parse($firstOrder)->startOfDay() : now()->startOfMonth();
            $endDate = now()->endOfDay();
        } else {
            $startDate = $request->input('start_date')
                ? Carbon::parse($request->input('start_date'))->startOfDay()
                : Carbon::today()->startOfMonth();
            $endDate = $request->input('end_date')
                ? Carbon::parse($request->input('end_date'))->endOfDay()
                : Carbon::today()->endOfDay();
        }

        $selectedStaffId = $request->input('staff_id');
        $netExpr = 'GREATEST(total_amount - COALESCE(discount_amount, 0) - COALESCE(exchange_credit, 0), 0)';

        $base = $this->completedOrdersQuery($shopId, $startDate, $endDate);

        $personLeaderboard = (clone $base)
            ->select(
                'user_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("COALESCE(SUM({$netExpr}), 0) as total_revenue"),
                DB::raw("COALESCE(AVG({$netExpr}), 0) as avg_ticket")
            )
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total_revenue')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                $row->rank = $index + 1;
                return $row;
            });

        $counterLeaderboard = (clone $base)
            ->select(
                'counter_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("COALESCE(SUM({$netExpr}), 0) as total_revenue"),
                DB::raw("COALESCE(AVG({$netExpr}), 0) as avg_ticket"),
                DB::raw('COUNT(DISTINCT user_id) as staff_count')
            )
            ->groupBy('counter_id')
            ->with('counter')
            ->orderByDesc('total_revenue')
            ->get()
            ->values()
            ->map(function ($row, $index) {
                $row->rank = $index + 1;
                return $row;
            });

        $periodTotal = (float) $personLeaderboard->sum('total_revenue');
        $periodOrders = (int) $personLeaderboard->sum('total_orders');
        $topPerson = $personLeaderboard->first();
        $topCounter = $counterLeaderboard->first();

        $staffList = \App\Models\User::where('shop_id', $shopId)->orderBy('name')->get();

        $logQuery = $this->completedOrdersQuery($shopId, $startDate, $endDate);
        if ($selectedStaffId) {
            $logQuery->where('user_id', $selectedStaffId);
        }

        $activityLog = $logQuery->select(
                DB::raw('DATE(created_at) as sale_date'),
                'user_id',
                'counter_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("SUM({$netExpr}) as total_revenue")
            )
            ->groupBy(DB::raw('DATE(created_at)'), 'user_id', 'counter_id')
            ->with(['user', 'counter'])
            ->orderByDesc(DB::raw('DATE(created_at)'))
            ->paginate(15)
            ->appends($request->query());

        $payload = compact(
            'view',
            'personLeaderboard',
            'counterLeaderboard',
            'activityLog',
            'periodTotal',
            'periodOrders',
            'topPerson',
            'topCounter',
            'startDate',
            'endDate',
            'staffList',
            'selectedStaffId',
        );

        if ($request->ajax()) {
            return view('reports.partials.staff-performance-live', $payload);
        }

        return view('reports.staff-performance', $payload);
    }

    // Modal API Endpoint
    public function staffDailyDetails(Request $request)
    {
        $this->ensureAdmin();

        $shopId = Auth::user()->shop_id;
        $staffId = $request->input('staff_id');
        $date = $request->input('date');
        $counterId = $request->input('counter_id');

        $parsedDate = \Carbon\Carbon::parse($date);
        $staff = \App\Models\User::where('shop_id', $shopId)->findOrFail($staffId);
        
        // Fetch Counter Name if it exists
        $counterName = 'N/A';
        if ($counterId) {
            $counter = \App\Models\Counter::where('shop_id', $shopId)->find($counterId);
            $counterName = $counter ? $counter->name : 'N/A';
        }

        // Fetch exact orders for this staff, on this date, at this counter
        $orders = \App\Models\Order::with('customer')
            ->where('shop_id', $shopId)
            ->where('user_id', $staffId)
            ->whereDate('created_at', $parsedDate->toDateString())
            ->when($counterId, function($q) use ($counterId) {
                return $q->where('counter_id', $counterId);
            })
            ->where('status', '!=', 'refunded')
            ->latest()
            ->get();

        return response()->json([
            'staff_name' => $staff->name,
            'counter_name' => $counterName,
            'date_formatted' => $parsedDate->format('l, F j, Y'),
            'total_orders' => $orders->count(),
            'total_revenue' => number_format($orders->sum('total_amount'), 2),
            'orders' => $orders->map(function($order) {
                return [
                    'time' => $order->created_at->format('h:i A'),
                    'id' => $order->id,
                    'customer' => $order->customer ? $order->customer->name : 'Walk-in Customer',
                    'amount' => number_format($order->total_amount, 2),
                ];
            })
        ]);
    }
}