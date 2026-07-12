<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Daily cash summary per POS counter.</p>
        </div>

        @include('accounts.partials.toolbar')

        <form action="{{ route('accounts.daily-summary') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Date</label>
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="border-gray-200 rounded-lg text-sm px-3 py-2">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Counter</label>
                <select name="counter_id" class="border-gray-200 rounded-lg text-sm px-3 py-2">
                    <option value="">All Counters</option>
                    @foreach($counters as $c)
                        <option value="{{ $c->id }}" @selected($counterId == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-slate-900 text-white text-xs font-bold px-4 py-2.5 rounded-lg">View</button>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @forelse($rows as $row)
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase">Counter</p>
                            <h3 class="text-lg font-black text-gray-900">{{ $row['counter']->name }}</h3>
                        </div>
                        <p class="text-xs text-gray-400">{{ $date->format('d M Y') }}</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-gray-500">Opening Balance</span>
                            <span class="font-bold">৳{{ number_format($row['opening'], 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-emerald-600">Sales (Cash In)</span>
                            <span class="font-bold text-emerald-600">+৳{{ number_format($row['sales_in'], 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-indigo-600">Transfers In</span>
                            <span class="font-bold text-indigo-600">+৳{{ number_format($row['transfers_in'], 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-rose-600">Transfers Out</span>
                            <span class="font-bold text-rose-600">-৳{{ number_format($row['transfers_out'], 2) }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-50">
                            <span class="text-rose-600">Refunds Out</span>
                            <span class="font-bold text-rose-600">-৳{{ number_format($row['refunds_out'], 2) }}</span>
                        </div>
                        <div class="flex justify-between py-3 bg-slate-50 rounded-xl px-3 mt-2">
                            <span class="font-bold text-gray-800">Closing Balance</span>
                            <span class="font-black text-indigo-600 text-lg">৳{{ number_format($row['closing'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-white border rounded-2xl p-8 text-center text-gray-400">
                    No counters found. Add counters under Settings to track multi-counter cash.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
