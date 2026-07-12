<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Integrated bookkeeping across all POS counters and online store.</p>
        </div>

        @include('accounts.partials.toolbar')

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('error') }}</div>
        @endif

        <form action="{{ route('accounts.opening-balance.update') }}" method="POST" class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            @csrf
            <div class="px-5 py-4 border-b bg-slate-50">
                <h3 class="font-bold text-gray-800">Opening Balances</h3>
                <p class="text-xs text-gray-500 mt-1">Set starting balances per account. Each counter has its own cash account.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-400">
                        <tr>
                            <th class="px-5 py-3 text-left">Account</th>
                            <th class="px-5 py-3 text-left">Type</th>
                            <th class="px-5 py-3 text-left">Counter</th>
                            <th class="px-5 py-3 text-right">Opening Balance</th>
                            <th class="px-5 py-3 text-right">Current Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($accounts as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium">{{ $row['account']->name }}</td>
                                <td class="px-5 py-3 capitalize text-gray-500">{{ $row['account']->type }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $row['account']->counter?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <input type="number" step="0.01" min="0" name="balances[{{ $row['account']->id }}]"
                                           value="{{ number_format($row['account']->opening_balance, 2, '.', '') }}"
                                           class="w-32 text-right border-gray-200 rounded-lg text-sm px-2 py-1.5">
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-indigo-600">৳{{ number_format($row['balance'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t bg-slate-50 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-indigo-700">Save Opening Balances</button>
            </div>
        </form>
    </div>
</x-app-layout>
