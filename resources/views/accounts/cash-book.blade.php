<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Cash book per counter — POS cash, petty cash, and payment wallets.</p>
        </div>

        @include('accounts.partials.toolbar')

        <form action="{{ route('accounts.cash-book') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            <input type="hidden" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Counter</label>
                <select name="counter_id" class="border-gray-200 rounded-lg text-sm px-3 py-2">
                    <option value="">All / Shop-level</option>
                    @foreach($counters as $c)
                        <option value="{{ $c->id }}" @selected(request('counter_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Cash Account</label>
                <select name="account_id" class="border-gray-200 rounded-lg text-sm px-3 py-2 min-w-[220px]">
                    @foreach($cashAccounts as $a)
                        <option value="{{ $a->id }}" @selected($account?->id === $a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-slate-900 text-white text-xs font-bold px-4 py-2.5 rounded-lg">Filter</button>
        </form>

        @if($account)
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl px-5 py-4 flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-indigo-400 uppercase">Current Balance</p>
                    <p class="text-2xl font-black text-indigo-700">{{ $account->name }}</p>
                </div>
                <p class="text-3xl font-black text-indigo-600">৳{{ number_format($balance, 2) }}</p>
            </div>

            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-400">
                        <tr>
                            <th class="px-5 py-3 text-left">Date</th>
                            <th class="px-5 py-3 text-left">Type</th>
                            <th class="px-5 py-3 text-left">Description</th>
                            <th class="px-5 py-3 text-left">Counter</th>
                            <th class="px-5 py-3 text-right">In</th>
                            <th class="px-5 py-3 text-right">Out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($entries as $entry)
                            <tr>
                                <td class="px-5 py-3 text-gray-500">{{ $entry->transaction->transaction_date->format('d M Y') }}</td>
                                <td class="px-5 py-3 capitalize">{{ str_replace('_', ' ', $entry->transaction->type) }}</td>
                                <td class="px-5 py-3">{{ $entry->transaction->description }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $entry->counter?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right text-emerald-600 font-medium">
                                    {{ $entry->entry_type === 'debit' ? '৳'.number_format($entry->amount, 2) : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right text-rose-600 font-medium">
                                    {{ $entry->entry_type === 'credit' ? '৳'.number_format($entry->amount, 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No cash movements in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
