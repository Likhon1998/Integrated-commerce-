@php
    $tabs = [
        'analytics.overview' => 'Overview',
        'analytics.orders' => 'Orders',
        'analytics.revenue' => 'Revenue',
        'analytics.expense' => 'Expense',
        'analytics.inventory' => 'Inventory',
        'analytics.balance' => 'Balance',
    ];
@endphp

<div class="flex flex-wrap gap-2">
    @foreach($tabs as $route => $label)
        <a href="{{ route($route, request()->only(['start_date', 'end_date', 'all_time'])) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs($route) ? 'bg-indigo-600 text-white shadow-md' : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<form action="{{ url()->current() }}" method="GET" class="flex flex-wrap items-end gap-2 mt-4">
    <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">From</label>
        <input type="date" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" class="border-gray-200 rounded-lg text-sm px-3 py-2">
    </div>
    <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">To</label>
        <input type="date" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" class="border-gray-200 rounded-lg text-sm px-3 py-2">
    </div>
    <button type="submit" class="bg-slate-900 text-white text-xs font-bold px-4 py-2.5 rounded-lg hover:bg-indigo-600">Apply</button>
    <a href="{{ route(request()->route()->getName(), ['all_time' => 1]) }}" class="bg-gray-100 text-gray-700 text-xs font-bold px-4 py-2.5 rounded-lg hover:bg-gray-200">All Time</a>
</form>
