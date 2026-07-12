<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesLedgerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Nexa POS Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::redirect('/login', '/admin/login');
Route::get('/shop', [WebsiteController::class, 'shop'])->name('website.shop');
Route::get('/category/{slug}', [WebsiteController::class, 'category'])->name('website.category');
Route::get('/brand/{slug}', [WebsiteController::class, 'brand'])->name('website.brand');
Route::get('/product/{product}', [WebsiteController::class, 'product'])->name('website.product');
Route::get('/track-order', [WebsiteController::class, 'trackOrderForm'])->name('website.track');
Route::post('/track-order', [WebsiteController::class, 'trackOrder'])->name('website.track.submit');
Route::post('/checkout', [WebsiteController::class, 'checkout'])->name('website.checkout');

Route::middleware(['auth', 'verified', \App\Http\Middleware\CheckIfSuspended::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/refresh-session', function () { return response()->json(['status' => 'Nexa POS Active']); });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.checkout');
    Route::get('/pos/receipt/{order}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/customer-lookup', [PosController::class, 'lookupCustomer'])->name('pos.customer-lookup');
    Route::post('/pos/sync-offline', [PosController::class, 'syncOffline'])->name('pos.sync');

    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class)->except(['show']);
    Route::resource('products', ProductController::class);
    Route::get('/products-import/csv', [ProductController::class, 'importForm'])->name('products.import');
    Route::post('/products-import/csv', [ProductController::class, 'importStore'])->name('products.import.store');
    Route::get('/products-barcodes/print', [ProductController::class, 'barcodes'])->name('products.barcodes');
    Route::get('/stock-ledger', [StockLedgerController::class, 'index'])->name('stock.index');
    Route::post('/stock-ledger', [StockLedgerController::class, 'store'])->name('stock.store');

    Route::get('/sales-ledger', [SalesLedgerController::class, 'index'])->name('sales.index');

    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/opening-balance', [AccountController::class, 'openingBalance'])->name('opening-balance');
        Route::post('/opening-balance', [AccountController::class, 'updateOpeningBalance'])->name('opening-balance.update');
        Route::get('/chart', [AccountController::class, 'chart'])->name('chart');
        Route::post('/chart', [AccountController::class, 'storeAccount'])->name('chart.store');
        Route::get('/ledger', [AccountController::class, 'ledger'])->name('ledger');
        Route::get('/cash-book', [AccountController::class, 'cashBook'])->name('cash-book');
        Route::get('/daily-summary', [AccountController::class, 'dailySummary'])->name('daily-summary');
        Route::get('/petty-cash', [AccountController::class, 'pettyCashForm'])->name('petty-cash');
        Route::post('/petty-cash', [AccountController::class, 'pettyCashStore'])->name('petty-cash.store');
        Route::get('/transfer', [AccountController::class, 'transferForm'])->name('transfer');
        Route::post('/transfer', [AccountController::class, 'transferStore'])->name('transfer.store');
    });

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/overview', [AnalyticsController::class, 'overview'])->name('overview');
        Route::get('/orders', [AnalyticsController::class, 'orders'])->name('orders');
        Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
        Route::get('/expense', [AnalyticsController::class, 'expense'])->name('expense');
        Route::get('/inventory', [AnalyticsController::class, 'inventory'])->name('inventory');
        Route::get('/balance', [AnalyticsController::class, 'balance'])->name('balance');
    });

    Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily');
    Route::get('/reports/best-sellers', [ReportController::class, 'bestSellers'])->name('reports.best_sellers');
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low_stock');
    Route::get('/reports/staff-performance', [ReportController::class, 'staffPerformance'])->name('reports.staff_performance');
    Route::get('/reports/staff-daily-details', [ReportController::class, 'staffDailyDetails'])->name('reports.staff_daily_details');
    Route::post('/orders/{order}/exchange', [ExchangeController::class, 'processExchange'])->name('orders.exchange');
    Route::post('/sales/{order}/refund', [SalesLedgerController::class, 'refund'])->name('sales.refund');

    Route::post('/ai-chat', [AiChatController::class, 'ask'])
        ->name('ai.chat')
        ->middleware('can:use ai chat');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::post('/staff/{staff}/toggle-suspend', [StaffController::class, 'toggleSuspend'])->name('staff.toggle-suspend');

    Route::resource('counters', CounterController::class)->except(['create', 'show', 'edit']);

    Route::get('/online-orders', [OnlineOrderController::class, 'index'])->name('online-orders.index');
    Route::post('/online-orders/{order}/status', [OnlineOrderController::class, 'updateStatus'])->name('online-orders.update-status');
});

require __DIR__.'/auth.php';
