<x-app-layout>
@php
    $user = Auth::user();
    $money = fn ($n) => 'Tk'.number_format((float) $n, 2);
    $statusClass = [
        'completed' => 'bg-emerald-50 text-emerald-700',
        'pending' => 'bg-amber-50 text-amber-700',
        'processing' => 'bg-amber-50 text-amber-700',
        'shipped' => 'bg-sky-50 text-sky-700',
        'cancelled' => 'bg-rose-50 text-rose-700',
        'returned' => 'bg-slate-100 text-slate-600',
        'refunded' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-[1.35rem] font-bold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="mt-0.5 text-slate-500">
                Welcome back, {{ explode(' ', $user->name)[0] }}!
                Here’s what’s happening with
                {{ !empty($isAdmin) ? 'your shop' : ($counter->name ?? 'your counter') }}
                today.
            </p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ $dateRangeLabel }}
        </div>
    </div>

    @if(!$user->canAccessPos())
        <div class="flex items-center justify-between rounded-2xl border border-amber-200/80 bg-amber-50 px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">!</div>
                <div>
                    <p class="text-xs font-bold text-amber-900">No sales counter assigned</p>
                    <p class="text-xs text-amber-700">Assign a counter before using the POS terminal.</p>
                </div>
            </div>
            @can('manage staff')
                <a href="{{ route('staff.index') }}" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800 hover:bg-amber-200">Assign</a>
            @endcan
        </div>
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ !empty($isAdmin) ? 'Total Sales' : 'My Sales' }}</p>
                    <p class="mt-2 text-xl font-bold text-slate-900 tracking-tight">{{ $money($weekSales ?? $todaySales) }}</p>
                    <p class="mt-1 text-[11px] font-semibold {{ ($salesChangePct ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ ($salesChangePct ?? 0) >= 0 ? '↑' : '↓' }} {{ abs($salesChangePct ?? 0) }}% vs last week
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Today: {{ $money($todaySales) }}</p>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Total Orders</p>
                    <p class="mt-2 text-xl font-bold text-slate-900 tracking-tight">{{ number_format($weekOrders ?? $todayOrdersCount) }}</p>
                    <p class="mt-1 text-[11px] font-semibold {{ ($ordersChangePct ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ ($ordersChangePct ?? 0) >= 0 ? '↑' : '↓' }} {{ abs($ordersChangePct ?? 0) }}% vs last week
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Today: {{ $todayOrdersCount }} orders</p>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ !empty($isAdmin) ? 'Total Customers' : 'My Customers' }}</p>
                    <p class="mt-2 text-xl font-bold text-slate-900 tracking-tight">{{ number_format($totalCustomers) }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ !empty($isAdmin) ? 'Registered in shop' : 'At this counter' }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Products</p>
                    <p class="mt-2 text-xl font-bold text-slate-900 tracking-tight">{{ number_format($totalProducts) }}</p>
                    <p class="mt-1 text-[11px] {{ ($lowStockCount ?? 0) > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }}">
                        {{ $lowStockCount }} low stock · Inv {{ $money($inventoryValue) }}
                    </p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Sales Overview</h3>
                    <p class="text-[11px] text-slate-400">This week vs previous week</p>
                </div>
                <div class="flex items-center gap-3 text-[11px] font-semibold">
                    <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="h-2 w-2 rounded-full bg-blue-500"></span>This week</span>
                    <span class="inline-flex items-center gap-1.5 text-slate-400"><span class="h-2 w-2 rounded-full bg-slate-300"></span>Last week</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="salesOverviewChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Sales by Category</h3>
            </div>
            <div class="relative mx-auto h-44 w-44">
                <canvas id="categoryDonutChart"></canvas>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Week</span>
                    <span class="text-sm font-bold text-slate-900">{{ $money($categorySalesTotal) }}</span>
                </div>
            </div>
            <ul class="mt-4 space-y-2">
                @forelse($categorySales as $row)
                    @php $pct = $categorySalesTotal > 0 ? round(($row->revenue / $categorySalesTotal) * 100, 1) : 0; @endphp
                    <li class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-700">{{ $row->category_name }}</span>
                        <span class="text-slate-500">{{ $money($row->revenue) }} · {{ $pct }}%</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-xs text-slate-400">No category sales this week.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-5">
        <div class="xl:col-span-3 rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-900">Recent Orders</h3>
                @can('view sales ledger')
                    <a href="{{ route('sales.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">View All</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50/80 text-[10px] uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Order ID</th>
                            <th class="px-5 py-3 text-left font-semibold">Customer</th>
                            <th class="px-5 py-3 text-left font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Amount</th>
                            <th class="px-5 py-3 text-right font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-3 font-bold text-slate-800">{{ $order->invoice_no }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $order->customer->name ?? 'Walk-in' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold capitalize {{ $statusClass[$order->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-slate-900">{{ $money($order->total_amount) }}</td>
                                <td class="px-5 py-3 text-right text-slate-500">{{ $order->created_at->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No recent orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-900">Top Products</h3>
                @can('manage inventory')
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">View All</a>
                @endcan
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($topProducts as $row)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 border border-slate-100">
                            @if($row->product?->image)
                                <img src="{{ public_storage_url($row->product->image) }}" alt="" class="h-full w-full object-cover">
                            @else
                                <span class="text-[10px] font-bold text-slate-400">{{ strtoupper(substr($row->product->name ?? 'P', 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-slate-800">{{ $row->product->name ?? 'Product' }}</p>
                            <p class="text-[11px] text-slate-400">{{ (int) $row->sold_qty }} sold</p>
                        </div>
                        <div class="text-xs font-bold text-slate-900">{{ $money($row->revenue) }}</div>
                    </li>
                @empty
                    <li class="px-5 py-10 text-center text-xs text-slate-400">No product sales yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if(!empty($isAdmin) && isset($counterBreakdown))
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-bold text-slate-900">Today by counter</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="pb-2 pr-4">Counter</th>
                        <th class="pb-2 pr-4 text-right">Sales</th>
                        <th class="pb-2 pr-4 text-right">Orders</th>
                        <th class="pb-2 text-right">Customers</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($counterBreakdown as $row)
                        <tr>
                            <td class="py-2.5 pr-4 font-semibold text-slate-800">{{ $row->name }}</td>
                            <td class="py-2.5 pr-4 text-right font-bold">{{ $money($row->sales_total) }}</td>
                            <td class="py-2.5 pr-4 text-right text-slate-600">{{ $row->orders_count }}</td>
                            <td class="py-2.5 text-right text-slate-600">{{ $row->customers_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-slate-400">No counters yet.</td></tr>
                    @endforelse
                    @if(!empty($onlineToday) && ($onlineToday->orders_count > 0 || $onlineToday->sales_total > 0))
                        <tr>
                            <td class="py-2.5 pr-4 italic text-slate-500">{{ $onlineToday->name }}</td>
                            <td class="py-2.5 pr-4 text-right font-bold text-slate-700">{{ $money($onlineToday->sales_total) }}</td>
                            <td class="py-2.5 pr-4 text-right text-slate-500">{{ $onlineToday->orders_count }}</td>
                            <td class="py-2.5 text-right text-slate-500">{{ $onlineToday->customers_count }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-3">
        @can('process pos sales')
        <a href="{{ route('pos.index') }}" target="_blank" rel="noopener" class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wide text-blue-700 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition">Launch POS Terminal</a>
        @endcan
        @can('manage inventory')
            <a href="{{ route('products.index') }}" class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wide text-emerald-700 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition">Manage Products</a>
        @elsecan('process sales returns')
            <a href="{{ route('supply.sales-returns.index') }}" class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3.5 text-center text-xs font-bold uppercase tracking-wide text-emerald-700 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition">Sales Return</a>
        @endcan
    </div>
</div>

@can('use ai chat')
<div x-data="{ open: false, message: '', loading: false, history: [] }" class="fixed bottom-6 right-6 z-50">
    <button @click="open = !open" class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg ring-4 ring-blue-100 hover:bg-blue-700">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
    </button>
    <div x-show="open" style="display:none" class="absolute bottom-16 right-0 flex h-[380px] w-80 flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl sm:w-96">
        <div class="flex items-center justify-between bg-slate-900 px-4 py-3 text-white">
            <div>
                <h3 class="text-sm font-bold">Nexa AI Assistant</h3>
                <p class="text-[10px] text-blue-300">Powered by Gemini</p>
            </div>
            <button @click="open = false" class="text-slate-400 hover:text-white">&times;</button>
        </div>
        <div class="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-4 text-sm">
            <div class="rounded-2xl bg-blue-50 p-3 text-blue-800">Ask about sales, products, or stock.</div>
            <template x-for="chat in history">
                <div class="flex flex-col space-y-1">
                    <div class="max-w-[85%] self-end rounded-2xl bg-blue-600 p-3 text-white" x-text="chat.user"></div>
                    <div class="max-w-[85%] self-start rounded-2xl border bg-white p-3 text-slate-700" x-html="chat.ai"></div>
                </div>
            </template>
        </div>
        <form class="flex gap-2 border-t bg-white p-3" @submit.prevent="
            if(message.trim()==='') return;
            history.push({user:message, ai:'...'});
            let currentMsg=message; message=''; loading=true;
            fetch('{{ route('ai.chat') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({message:currentMsg})})
            .then(r=>r.json()).then(d=>{history[history.length-1].ai=d.reply?d.reply.replace(/\n/g,'<br>'):'Sorry, something went wrong.';loading=false;})
            .catch(()=>{history[history.length-1].ai='Access Denied.';loading=false;});
        ">
            <input x-model="message" type="text" placeholder="Type your message..." class="w-full rounded-xl border-slate-200 text-sm">
            <button class="rounded-xl bg-blue-600 px-4 text-sm font-bold text-white">Send</button>
        </form>
    </div>
</div>
@endcan

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const labels = @json($salesChartLabels);
    const thisWeek = @json($salesChartThisWeek);
    const lastWeek = @json($salesChartLastWeek);
    const catLabels = @json($categorySales->pluck('category_name'));
    const catValues = @json($categorySales->pluck('revenue')->map(fn($v)=>(float)$v));

    const salesEl = document.getElementById('salesOverviewChart');
    if (salesEl) {
        new Chart(salesEl, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'This Week',
                        data: thisWeek,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointBackgroundColor: '#2563eb',
                    },
                    {
                        label: 'Last Week',
                        data: lastWeek,
                        borderColor: '#cbd5e1',
                        borderDash: [6, 5],
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 } }, beginAtZero: true },
                },
            },
        });
    }

    const donutEl = document.getElementById('categoryDonutChart');
    if (donutEl) {
        new Chart(donutEl, {
            type: 'doughnut',
            data: {
                labels: catLabels.length ? catLabels : ['No data'],
                datasets: [{
                    data: catValues.length ? catValues : [1],
                    backgroundColor: ['#2563eb', '#14b8a6', '#f59e0b', '#8b5cf6', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { enabled: catValues.length > 0 } },
            },
        });
    }
})();
</script>
</x-app-layout>
