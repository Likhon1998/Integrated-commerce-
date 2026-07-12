<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Inventory Analytics</h2>
            <p class="text-sm text-gray-500 mt-1">Stock health, value, and movement from your inventory catalog.</p>
        </div>

        @include('analytics.partials.toolbar')

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach([
                ['Products', $inventory['total_products']],
                ['Units in Stock', number_format($inventory['total_units'])],
                ['Cost Value', '৳'.number_format($inventory['cost_value'], 2)],
                ['Retail Value', '৳'.number_format($inventory['retail_value'], 2)],
                ['Low Stock', $inventory['low_stock']],
                ['Out of Stock', $inventory['out_of_stock']],
            ] as [$label, $value])
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">{{ $label }}</p>
                    <p class="text-xl font-black text-gray-900 mt-2">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold mb-3">Top Selling (period)</h3>
                @forelse($topSelling as $row)
                    <div class="flex justify-between py-2 border-b border-gray-50 text-sm">
                        <span>{{ $row->product->name ?? 'Unknown' }}</span>
                        <span class="font-bold">{{ $row->sold }} sold</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No sales data.</p>
                @endforelse
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold mb-3 text-rose-600">Low Stock Alerts</h3>
                @forelse($lowStock as $product)
                    <div class="flex justify-between py-2 border-b border-gray-50 text-sm">
                        <span>{{ $product->name }}</span>
                        <span class="font-bold text-rose-600">{{ $product->stock_quantity }} left</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">All stock levels are healthy.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white border rounded-2xl shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Category</th>
                        <th class="px-5 py-3 text-left">Products</th>
                        <th class="px-5 py-3 text-left">Units</th>
                        <th class="px-5 py-3 text-right">Stock Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr class="border-t border-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $cat->category->name ?? 'Uncategorized' }}</td>
                            <td class="px-5 py-3">{{ $cat->products }}</td>
                            <td class="px-5 py-3">{{ $cat->units }}</td>
                            <td class="px-5 py-3 text-right font-bold">৳{{ number_format($cat->cost_value, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No inventory data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
