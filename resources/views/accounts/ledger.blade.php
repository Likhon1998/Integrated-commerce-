<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Account ledger with running balance — filter by account and counter.</p>
        </div>

        @include('accounts.partials.toolbar')

        <form action="{{ route('accounts.ledger') }}" method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            <input type="hidden" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Account</label>
                <select name="account_id" class="border-gray-200 rounded-lg text-sm px-3 py-2 min-w-[200px]">
                    @foreach($accountList as $a)
                        <option value="{{ $a->id }}" @selected($account?->id === $a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-slate-900 text-white text-xs font-bold px-4 py-2.5 rounded-lg">Filter</button>
        </form>

        @if($account)
            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b">
                    <h3 class="font-bold text-gray-800">{{ $account->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $account->code }} · {{ ucfirst($account->type) }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-400">
                            <tr>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Description</th>
                                <th class="px-5 py-3 text-left">Counter</th>
                                <th class="px-5 py-3 text-right">Debit</th>
                                <th class="px-5 py-3 text-right">Credit</th>
                                <th class="px-5 py-3 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($entries as $row)
                                <tr>
                                    <td class="px-5 py-3 text-gray-500">{{ $row['entry']->transaction->transaction_date->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ $row['entry']->transaction->description }}</td>
                                    <td class="px-5 py-3 text-gray-500">{{ $row['entry']->counter?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right">{{ $row['debit'] > 0 ? '৳'.number_format($row['debit'], 2) : '—' }}</td>
                                    <td class="px-5 py-3 text-right">{{ $row['credit'] > 0 ? '৳'.number_format($row['credit'], 2) : '—' }}</td>
                                    <td class="px-5 py-3 text-right font-bold">৳{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No entries in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
