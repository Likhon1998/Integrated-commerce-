<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Analytics</h2>
            <p class="text-sm text-gray-500 mt-1">POS terminal + online store performance in one place.</p>
        </div>

        @include('analytics.partials.toolbar')

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Total Revenue</p>
                <p class="text-2xl font-black text-indigo-600 mt-2">৳{{ number_format($revenue, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $orders }} orders</p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Cost of Goods</p>
                <p class="text-2xl font-black text-rose-600 mt-2">৳{{ number_format($expense, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">Inventory sold cost</p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Gross Profit</p>
                <p class="text-2xl font-black text-emerald-600 mt-2">৳{{ number_format($revenue - $expense, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $revenue > 0 ? number_format((($revenue - $expense) / $revenue) * 100, 1) : 0 }}% margin</p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Stock Value</p>
                <p class="text-2xl font-black text-gray-900 mt-2">৳{{ number_format($inventory['retail_value'], 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $inventory['low_stock'] }} low stock alerts</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-4">Sales Channel Split</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                        <div>
                            <p class="font-bold text-sm">POS Terminal</p>
                            <p class="text-xs text-gray-500">{{ $posCount }} orders</p>
                        </div>
                        <p class="font-black text-indigo-600">৳{{ number_format($posRevenue, 2) }}</p>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
                        <div>
                            <p class="font-bold text-sm">Online Store</p>
                            <p class="text-xs text-gray-500">{{ $webCount }} orders</p>
                        </div>
                        <p class="font-black text-emerald-600">৳{{ number_format($webRevenue, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-4">Top Selling Products</h3>
                <div class="space-y-2">
                    @forelse($topProducts as $row)
                        <div class="flex justify-between text-sm py-2 border-b border-gray-50">
                            <span class="font-medium">{{ $row->product->name ?? 'Unknown' }}</span>
                            <span class="font-bold text-gray-600">{{ $row->sold }} sold</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No sales in this period.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm overflow-x-auto">
            <h3 class="font-bold text-gray-800 mb-4">Daily Revenue Trend</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-400 text-xs uppercase"><th class="pb-2">Date</th><th class="pb-2">Orders</th><th class="pb-2">Revenue</th></tr></thead>
                <tbody>
                    @forelse($chart as $day)
                        <tr class="border-t border-gray-50">
                            <td class="py-2 font-medium">{{ \Carbon\Carbon::parse($day->day)->format('d M Y') }}</td>
                            <td class="py-2">{{ $day->orders }}</td>
                            <td class="py-2 font-bold text-indigo-600">৳{{ number_format($day->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-gray-500">No data for selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
