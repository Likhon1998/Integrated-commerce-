<?php

namespace App\Http\Controllers;

use App\Models\{Order, Product, Customer};
use App\Services\StockService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop;
        $shopId = $user->shop_id;

        app(StockService::class)->ensureDefaultLocations($shopId);

        $queryOrders = Order::where('shop_id', $shopId);
        $queryProducts = Product::where('shop_id', $shopId);
        $queryCustomers = Customer::where('shop_id', $shopId);

        $todaySales = (clone $queryOrders)->whereDate('created_at', Carbon::today())->sum('total_amount');
        $todayOrdersCount = (clone $queryOrders)->whereDate('created_at', Carbon::today())->count();
        $totalCustomers = $queryCustomers->count();
        $lowStockCount = (clone $queryProducts)->where('stock_quantity', '<=', 5)->count();
        $inventoryValue = (clone $queryProducts)->selectRaw('SUM(selling_price * stock_quantity) as total_value')->value('total_value') ?? 0;

        return view('dashboard', compact(
            'shop', 'todaySales',
            'todayOrdersCount', 'totalCustomers', 'lowStockCount',
            'inventoryValue'
        ));
    }
}
