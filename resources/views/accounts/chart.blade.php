<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Chart of accounts — system + custom accounts per shop.</p>
        </div>

        @include('accounts.partials.toolbar')

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                @foreach(['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity', 'income' => 'Income', 'expense' => 'Expenses'] as $type => $label)
                    @if(($grouped[$type] ?? collect())->isNotEmpty())
                        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-5 py-3 border-b bg-slate-50 font-bold text-gray-800">{{ $label }}</div>
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($grouped[$type] as $row)
                                        <tr>
                                            <td class="px-5 py-3">
                                                <p class="font-medium">{{ $row['account']->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $row['account']->code }}</p>
                                            </td>
                                            <td class="px-5 py-3 text-gray-500 text-xs">
                                                {{ $row['account']->counter?->name ?? ($row['account']->is_system ? 'System' : 'Custom') }}
                                            </td>
                                            <td class="px-5 py-3 text-right font-bold">৳{{ number_format($row['balance'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="bg-white border rounded-2xl shadow-sm p-5 h-fit">
                <h3 class="font-bold text-gray-800 mb-4">Add Custom Account</h3>
                <form action="{{ route('accounts.chart.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Name</label>
                        <input type="text" name="name" required class="w-full border-gray-200 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Type</label>
                        <select name="type" class="w-full border-gray-200 rounded-lg text-sm px-3 py-2">
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white text-sm font-bold py-2.5 rounded-xl hover:bg-indigo-700">Add Account</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
