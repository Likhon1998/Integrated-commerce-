<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $shopId = Auth::user()->shop_id;

        if (mb_strlen($q) < 1) {
            return response()->json(['groups' => []]);
        }

        $like = '%'.mb_strtolower(str_replace(['%', '_'], ['\\%', '\\_'], $q)).'%';
        $groups = [];

        if (Gate::allows('manage inventory') || Gate::allows('process pos sales')) {
            $products = Product::where('shop_id', $shopId)
                ->where(function ($query) use ($like) {
                    $this->looseMatch($query, 'name', $like);
                    $query->orWhere(function ($inner) use ($like) {
                        $this->looseMatch($inner, 'sku', $like);
                    });
                    $query->orWhere(function ($inner) use ($like) {
                        $this->looseMatch($inner, 'barcode', $like);
                    });
                })
                ->orderBy('name')
                ->limit(6)
                ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'stock_quantity', 'image']);

            if ($products->isNotEmpty()) {
                $groups[] = [
                    'key' => 'products',
                    'label' => 'Products',
                    'items' => $products->map(fn (Product $p) => [
                        'id' => 'product-'.$p->id,
                        'title' => $p->name,
                        'subtitle' => trim(collect([
                            $p->sku ? 'SKU '.$p->sku : null,
                            $p->barcode ? 'Barcode '.$p->barcode : null,
                            'Stock '.(int) $p->stock_quantity,
                        ])->filter()->implode(' · ')),
                        'meta' => '৳'.number_format((float) $p->selling_price, 2),
                        'url' => Gate::allows('manage inventory')
                            ? route('products.edit', $p)
                            : route('products.index', ['q' => $p->name]),
                        'icon' => 'product',
                        'image' => public_storage_url($p->image),
                    ])->values(),
                ];
            }
        }

        $ordersQuery = Order::where('shop_id', $shopId)
            ->with('customer:id,name,phone')
            ->where(function ($query) use ($like, $q) {
                $this->looseMatch($query, 'invoice_no', $like);
                if (ctype_digit($q)) {
                    $query->orWhere('id', (int) $q);
                }
                $query->orWhereHas('customer', function ($c) use ($like) {
                    $this->looseMatch($c, 'name', $like);
                    $c->orWhere(function ($inner) use ($like) {
                        $this->looseMatch($inner, 'phone', $like);
                    });
                });
            });

        // Counter staff can search POS sales across all counters, but not online orders.
        if (! Auth::user()->isAdminUser()) {
            $ordersQuery->whereNotNull('counter_id');
        }

        $orders = $ordersQuery
            ->latest()
            ->limit(6)
            ->get(['id', 'invoice_no', 'total_amount', 'status', 'counter_id', 'customer_id', 'created_at']);

        if ($orders->isNotEmpty()) {
            $groups[] = [
                'key' => 'orders',
                'label' => 'Orders',
                'items' => $orders->map(function (Order $order) {
                    $isOnline = $order->isOnlineOrder();
                    $url = $isOnline && Auth::user()->isAdminUser()
                        ? route('online-orders.show', $order)
                        : (Gate::allows('view sales ledger')
                            ? route('sales.index', ['channel' => 'physical'])
                            : route('pos.receipt', $order));

                    return [
                        'id' => 'order-'.$order->id,
                        'title' => $order->invoice_no,
                        'subtitle' => trim(collect([
                            $order->customer?->name ?? 'Walk-in',
                            ucfirst($order->status),
                            $isOnline ? 'Online' : 'POS',
                            optional($order->created_at)->format('M j, Y'),
                        ])->filter()->implode(' · ')),
                        'meta' => '৳'.number_format((float) $order->total_amount, 2),
                        'url' => $url,
                        'icon' => 'order',
                        'image' => null,
                    ];
                })->values(),
            ];
        }

        $customers = Customer::where('shop_id', $shopId)
            ->where(function ($query) use ($like) {
                $this->looseMatch($query, 'name', $like);
                $query->orWhere(function ($inner) use ($like) {
                    $this->looseMatch($inner, 'phone', $like);
                });
                $query->orWhere(function ($inner) use ($like) {
                    $this->looseMatch($inner, 'email', $like);
                });
            })
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'name', 'phone', 'email']);

        if ($customers->isNotEmpty()) {
            $groups[] = [
                'key' => 'customers',
                'label' => 'Customers',
                'items' => $customers->map(fn (Customer $c) => [
                    'id' => 'customer-'.$c->id,
                    'title' => $c->name ?: 'Customer',
                    'subtitle' => trim(collect([$c->phone, $c->email])->filter()->implode(' · ')),
                    'meta' => null,
                    'url' => route('customers.edit', $c),
                    'icon' => 'customer',
                    'image' => null,
                ])->values(),
            ];
        }

        return response()->json(['groups' => $groups]);
    }

    protected function looseMatch($query, string $column, string $like): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            $query->where($column, 'ilike', $like);

            return;
        }

        $query->whereRaw("LOWER({$column}) LIKE ?", [$like]);
    }
}
