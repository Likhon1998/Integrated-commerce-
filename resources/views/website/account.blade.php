@extends('website.layout')

@section('title', 'My Account — ' . ($settings->store_name ?? 'Store'))

@section('content')
@php
    $currency = $settings->currency_symbol ?? 'Tk';
    $firstName = explode(' ', trim($customer?->name ?? auth()->user()->name ?? 'Customer'))[0] ?? 'Customer';
    $orderDetailsMap = ($recentOrders ?? collect())->mapWithKeys(function ($order) use ($orderTracking, $customer) {
        $track = $orderTracking[$order->id] ?? null;

        return [
            $order->id => [
                'id' => $order->id,
                'invoice' => $order->invoice_no,
                'date' => $order->created_at->format('M j, Y'),
                'datetime' => $order->created_at->format('M j, Y g:i A'),
                'status' => $order->status,
                'status_label' => $track['status_label'] ?? ucfirst($order->status),
                'total' => number_format((float) $order->total_amount, 2),
                'where' => $track['where_is_product'] ?? '',
                'courier' => $order->shipping_courier,
                'tracking_no' => $order->shipping_tracking_no,
                'address' => $customer?->address ?: 'No address saved yet.',
                'timeline' => $track['timeline'] ?? [],
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product?->name ?? 'Product',
                    'qty' => (int) $item->quantity,
                    'subtotal' => number_format((float) $item->subtotal, 2),
                ])->values()->all(),
                'track_url' => route('website.track', ['invoice' => $order->invoice_no]),
            ],
        ];
    });
@endphp
<div class="max-w-[1280px] mx-auto px-4 md:px-5 py-6"
     x-data="{
        openOrder: {{ $activeOrder?->id ?? 'null' }},
        detailOpen: false,
        detail: null,
        orders: @js($orderDetailsMap),
        openDetail(id) {
            this.detail = this.orders[id] || null;
            this.detailOpen = !!this.detail;
        },
        closeDetail() {
            this.detailOpen = false;
            this.detail = null;
        },
        statusClass(status) {
            if (status === 'completed') return 'bg-emerald-100 text-emerald-700';
            if (status === 'shipped') return 'bg-blue-100 text-blue-700';
            if (status === 'processing') return 'bg-indigo-100 text-indigo-700';
            if (['cancelled','returned','refunded'].includes(status)) return 'bg-rose-100 text-rose-700';
            return 'bg-amber-100 text-amber-700';
        }
     }"
     x-init="setInterval(() => { if (document.visibilityState === 'visible' && !detailOpen) window.location.reload(); }, 30000)"
     @keydown.escape.window="closeDetail()">
    <div class="grid gap-4 xl:grid-cols-[250px_minmax(0,1fr)]">
        <div class="space-y-4">
            @include('website.partials.account-sidebar', ['activeMenu' => 'dashboard', 'customer' => $customer, 'activeOrder' => $activeOrder])
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[15px] font-bold text-slate-900">Exclusive Member Benefits</p>
                <p class="mt-1 text-[11px] text-slate-500">Get special offers and faster checkout.</p>
                <a href="{{ route('website.shop') }}" class="mt-3 inline-flex text-[13px] font-semibold text-blue-600 hover:text-blue-700">Explore Benefits →</a>
            </div>
        </div>

        <section class="space-y-4">
            @if(session('profile_success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-semibold text-emerald-700">
                    {{ session('profile_success') }}
                </div>
            @endif

            <div>
                <h1 class="text-[31px] font-extrabold tracking-tight text-slate-900">Welcome back, {{ $firstName }}!</h1>
                <p class="mt-0.5 text-[13px] text-slate-500">Here’s what’s happening with your account today.</p>
            </div>

            <div class="grid gap-3 md:grid-cols-2 2xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Total Orders</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>
                            <p class="text-[28px] font-black leading-none text-slate-900">{{ $totalOrders }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">View all orders</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">In Transit</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>
                            <p class="text-[28px] font-black leading-none text-slate-900">{{ $inTransitOrders }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">Track your orders</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17h6m-8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h9a2 2 0 012 2v2m0 8h1a2 2 0 002-2v-3m-3 5a2 2 0 11-4 0m4 0a2 2 0 104 0m-4 0H9m10-8l-2-3h-3"/></svg>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Delivered</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>
                            <p class="text-[28px] font-black leading-none text-slate-900">{{ $deliveredOrders }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">View order history</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-50 text-lime-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Refunds</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div>
                            <p class="text-[28px] font-black leading-none text-slate-900">{{ $refundOrders }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">View details</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v6h6M20 20v-6h-6M5 14a7 7 0 0012 2M19 10A7 7 0 007 8"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[minmax(0,1.52fr)_340px]">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-[15px] font-bold text-slate-900">Track Your Order</h2>
                            <p class="text-[11px] text-slate-500">See the latest update from our store to your doorstep.</p>
                        </div>
                        <a href="{{ route('website.track', ['invoice' => $activeOrder?->invoice_no]) }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-700">View All Orders →</a>
                    </div>

                    @if($activeOrder && $activeTracking)
                        <div class="rounded-2xl border border-slate-100 bg-white">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-[13px] font-bold text-slate-900">{{ $activeOrder->invoice_no }}</p>
                                    <p class="text-[11px] text-slate-500">Placed on {{ $activeOrder->created_at->format('M j, Y') }}</p>
                                </div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold
                                    @if($activeOrder->status === 'completed') bg-emerald-100 text-emerald-700
                                    @elseif($activeOrder->status === 'shipped') bg-blue-100 text-blue-700
                                    @elseif($activeOrder->status === 'processing') bg-indigo-100 text-indigo-700
                                    @else bg-amber-100 text-amber-700 @endif">
                                    {{ $activeTracking['status_label'] }}
                                </span>
                            </div>

                            <div class="mt-5 grid gap-2 md:grid-cols-{{ count($activeTracking['timeline']) > 1 ? count($activeTracking['timeline']) : 1 }}">
                                @foreach($activeTracking['timeline'] as $step)
                                    <div class="relative">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-[11px] font-bold {{ !empty($step['done']) ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-400' }}">
                                                {{ !empty($step['done']) ? '✓' : '•' }}
                                            </div>
                                            <p class="mt-2 text-[11px] font-bold text-slate-800">{{ $step['label'] }}</p>
                                            <p class="mt-1 text-[11px] text-slate-500">{{ $step['at'] ?? 'Waiting' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                                <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white text-xl">📦</div>
                                    <div class="min-w-0">
                                        <p class="truncate text-[13px] font-bold text-slate-900">
                                            {{ $activeOrder->items->first()?->product?->name ?? 'Order Item' }}
                                            @if($activeOrder->items->count() > 1)
                                                +{{ $activeOrder->items->count() - 1 }} more
                                            @endif
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-slate-500">
                                            Qty:
                                            {{ $activeOrder->items->sum('quantity') }}
                                        </p>
                                        <p class="mt-1 text-[11px] font-medium text-blue-700">{{ $activeTracking['where_is_product'] }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('website.track', ['invoice' => $activeOrder->invoice_no]) }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-[13px] font-semibold text-blue-700 hover:bg-blue-50">Track Live</a>
                            </div>

                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Current Location</p>
                                    <p class="mt-1 text-[13px] font-semibold text-slate-800">
                                        {{ $activeOrder->shipping_courier ? $activeOrder->shipping_courier : 'Our store / packing desk' }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Delivery Address</p>
                                    <p class="mt-1 line-clamp-2 text-[13px] font-semibold text-slate-800">{{ $customer?->address ?? 'No address saved yet.' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                            <p class="text-[13px] font-semibold text-slate-700">No active order yet.</p>
                            <p class="mt-1 text-xs text-slate-500">Place your first order and live tracking will appear here.</p>
                            <a href="{{ route('website.shop') }}" class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-[13px] font-semibold text-white hover:bg-blue-700">Start Shopping</a>
                        </div>
                    @endif
                    </div>

                    <div id="recent-orders" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-[15px] font-bold text-slate-900">Recent Orders</h2>
                    </div>
                    <a href="{{ route('website.track') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-700">View All Orders →</a>
                </div>

                @if($recentOrders->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <p class="text-[13px] font-semibold text-slate-700">No orders yet.</p>
                        <p class="mt-1 text-xs text-slate-500">When you place a website order, it will appear here immediately.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-[13px]">
                            <thead class="border-b border-slate-100 text-[10px] uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="px-2 py-2.5 text-left">Order ID</th>
                                    <th class="px-2 py-2.5 text-left">Date</th>
                                    <th class="px-2 py-2.5 text-left">Items</th>
                                    <th class="px-2 py-2.5 text-right">Total</th>
                                    <th class="px-2 py-2.5 text-left">Status</th>
                                    <th class="px-2 py-2.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($recentOrders as $order)
                                    @php $track = $orderTracking[$order->id] ?? null; @endphp
                                    <tr>
                                        <td class="px-2 py-3 font-semibold text-slate-900">{{ $order->invoice_no }}</td>
                                        <td class="px-2 py-3 text-slate-600">{{ $order->created_at->format('M j, Y') }}</td>
                                        <td class="px-2 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-base">📦</div>
                                                <div class="min-w-0">
                                                    <p class="truncate font-medium text-slate-800">{{ $order->items->first()?->product?->name ?? 'Product' }}</p>
                                                    <p class="text-[11px] text-slate-500">{{ $order->items->sum('quantity') }} item(s)</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-3 text-right font-bold text-slate-900">{{ $currency }}{{ number_format($order->total_amount, 2) }}</td>
                                        <td class="px-2 py-3">
                                            <span class="inline-flex rounded-full px-2 py-1 text-[10px] font-bold
                                                @if($order->status === 'completed') bg-emerald-100 text-emerald-700
                                                @elseif($order->status === 'shipped') bg-blue-100 text-blue-700
                                                @elseif($order->status === 'processing') bg-indigo-100 text-indigo-700
                                                @elseif(in_array($order->status, ['cancelled', 'returned', 'refunded'])) bg-rose-100 text-rose-700
                                                @else bg-amber-100 text-amber-700 @endif">
                                                {{ $track['status_label'] ?? ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-3 text-right">
                                            <button type="button"
                                                    @click="openDetail({{ $order->id }})"
                                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">
                                                {{ $order->status === 'shipped' ? 'Track Order' : 'View Details' }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h2 class="text-[15px] font-bold text-slate-900">Account Overview</h2>
                        <div class="mt-3 space-y-3 text-[13px]">
                            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Name</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ $customer?->name ?? auth()->user()->name }}</p>
                                </div>
                                <a href="{{ route('website.account.profile.edit') }}" class="text-slate-400 hover:text-blue-600" title="Edit">✎</a>
                            </div>
                            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Email</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('website.account.profile.edit') }}" class="text-slate-400 hover:text-blue-600" title="Edit">✎</a>
                            </div>
                            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400">Phone</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ $customer?->phone ?: 'Not added yet' }}</p>
                                </div>
                                <a href="{{ route('website.account.profile.edit') }}" class="text-slate-400 hover:text-blue-600" title="Edit">✎</a>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Member Since</p>
                                <p class="mt-1 font-semibold text-slate-900">{{ $memberSince ?? now()->format('M j, Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('website.account.profile.edit') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">View Account Details</a>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-[15px] font-bold text-slate-900">Shipping Addresses</h2>
                            <span class="text-[11px] font-semibold text-blue-600">+ Add New</span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-[13px] font-bold text-slate-900">Home</p>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Default</span>
                            </div>
                            <p class="text-[13px] text-slate-700">{{ $customer?->address ?: 'No address saved yet. It will be saved on your next checkout.' }}</p>
                            @if($customer?->phone)
                                <p class="mt-2 text-[11px] font-medium text-slate-600">{{ $customer->phone }}</p>
                            @endif
                        </div>
                        <button type="button" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">View All Addresses</button>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-[15px] font-bold text-slate-900">Payment Methods</h2>
                            <span class="text-[11px] font-semibold text-blue-600">+ Add New</span>
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-[13px] font-bold text-slate-900">Cash on Delivery</p>
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700">Active</span>
                            </div>
                            <p class="text-[11px] text-slate-500">Used for current website checkout and synced with admin order updates.</p>
                        </div>
                        <button type="button" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">View All Payment Methods</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Order details modal --}}
    <div x-show="detailOpen" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4" @keydown.escape.window="closeDetail()">
        <div class="absolute inset-0 bg-slate-900/50" @click="closeDetail()"></div>
        <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl"
             @click.outside="closeDetail()"
             x-show="detailOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
            <template x-if="detail">
                <div>
                    <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-slate-100 bg-white px-5 py-4">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Order details</p>
                            <h3 class="mt-0.5 truncate text-[18px] font-extrabold text-slate-900" x-text="detail.invoice"></h3>
                            <p class="mt-0.5 text-[12px] text-slate-500" x-text="'Placed ' + detail.datetime"></p>
                        </div>
                        <button type="button" @click="closeDetail()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-4 px-5 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold" :class="statusClass(detail.status)" x-text="detail.status_label"></span>
                            <p class="text-[15px] font-extrabold text-slate-900" x-text="'{{ $currency }}' + detail.total"></p>
                        </div>

                        <p class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2.5 text-[12px] font-medium text-blue-800" x-text="detail.where" x-show="detail.where"></p>

                        <div x-show="detail.timeline && detail.timeline.length" class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">Tracking</p>
                            <div class="space-y-2.5">
                                <template x-for="(step, idx) in detail.timeline" :key="step.key + '-' + idx">
                                    <div class="flex gap-2.5">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                                              :class="step.active ? 'bg-blue-600 text-white' : (step.done ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500')"
                                              x-text="step.done && !step.active ? '✓' : (idx + 1)"></span>
                                        <div class="min-w-0">
                                            <p class="text-[12px] font-bold text-slate-800" x-text="step.label"></p>
                                            <p class="text-[11px] text-slate-500" x-text="step.at || 'Waiting…'"></p>
                                            <p class="text-[11px] text-slate-600" x-show="step.note" x-text="step.note"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">Items</p>
                            <div class="divide-y divide-slate-100 rounded-xl border border-slate-100">
                                <template x-for="(item, i) in detail.items" :key="'item-' + i">
                                    <div class="flex items-start justify-between gap-3 px-3 py-2.5 text-[13px]">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900" x-text="item.name"></p>
                                            <p class="text-[11px] text-slate-500" x-text="'Qty ' + item.qty"></p>
                                        </div>
                                        <p class="shrink-0 font-bold text-slate-900" x-text="'{{ $currency }}' + item.subtotal"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Courier</p>
                                <p class="mt-1 text-[12px] font-semibold text-slate-800" x-text="detail.courier || 'Our store / packing desk'"></p>
                                <p class="mt-0.5 font-mono text-[11px] text-slate-500" x-show="detail.tracking_no" x-text="detail.tracking_no"></p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Delivery address</p>
                                <p class="mt-1 line-clamp-3 text-[12px] font-semibold text-slate-800" x-text="detail.address"></p>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 flex flex-wrap gap-2 border-t border-slate-100 bg-white px-5 py-3">
                        <a :href="detail.track_url" class="gaget-btn-primary flex-1 text-center text-[13px] py-2.5">Open live tracking</a>
                        <button type="button" @click="closeDetail()" class="gaget-btn-outline flex-1 text-[13px] py-2.5">Close</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
