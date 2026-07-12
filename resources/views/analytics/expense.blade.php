<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Expense Analytics</h2>
            <p class="text-sm text-gray-500 mt-1">Cost of goods sold (COGS) from inventory used in POS and web orders.</p>
        </div>

        @include('analytics.partials.toolbar')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-5">
                <p class="text-[10px] font-bold text-rose-400 uppercase">Total COGS</p>
                <p class="text-2xl font-black text-rose-700 mt-2">৳{{ number_format($cogs, 2) }}</p>
            </div>
            <div class="bg-white border rounded-2xl p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Revenue (same period)</p>
                <p class="text-2xl font-black text-indigo-600 mt-2">৳{{ number_format($revenue, 2) }}</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
                <p class="text-[10px] font-bold text-emerald-500 uppercase">Gross Profit</p>
                <p class="text-2xl font-black text-emerald-700 mt-2">৳{{ number_format($revenue - $cogs, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border rounded-2xl shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Product</th>
                        <th class="px-5 py-3 text-left">Qty Sold</th>
                        <th class="px-5 py-3 text-left">Cost (COGS)</th>
                        <th class="px-5 py-3 text-right">Sales Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productCosts as $row)
                        <tr class="border-t border-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $row->name }}</td>
                            <td class="px-5 py-3">{{ $row->qty_sold }}</td>
                            <td class="px-5 py-3 text-rose-600 font-bold">৳{{ number_format($row->cost_total, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold">৳{{ number_format($row->sales_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No sold products in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $productCosts->links() }}</div>
        </div>
    </div>
</x-app-layout>
