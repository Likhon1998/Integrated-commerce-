<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Revenue Analytics</h2>
            <p class="text-sm text-gray-500 mt-1">Sales income from POS and your online store.</p>
        </div>

        @include('analytics.partials.toolbar')

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-900 text-white rounded-2xl p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Total Revenue</p>
                <p class="text-2xl font-black mt-2">৳{{ number_format($summary->total_revenue ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border rounded-2xl p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase">POS Revenue</p>
                <p class="text-2xl font-black text-indigo-600 mt-2">৳{{ number_format($summary->pos_revenue ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border rounded-2xl p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Web Revenue</p>
                <p class="text-2xl font-black text-emerald-600 mt-2">৳{{ number_format($summary->web_revenue ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border rounded-2xl p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Cash Collected</p>
                <p class="text-2xl font-black text-gray-900 mt-2">৳{{ number_format($summary->cash_total ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border rounded-2xl shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Orders</th>
                        <th class="px-5 py-3 text-left">POS</th>
                        <th class="px-5 py-3 text-left">Web</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($daily as $row)
                        <tr class="border-t border-gray-50">
                            <td class="px-5 py-3 font-medium">{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</td>
                            <td class="px-5 py-3">{{ $row->orders }}</td>
                            <td class="px-5 py-3 text-indigo-600">৳{{ number_format($row->pos_revenue, 2) }}</td>
                            <td class="px-5 py-3 text-emerald-600">৳{{ number_format($row->web_revenue, 2) }}</td>
                            <td class="px-5 py-3 text-right font-bold">৳{{ number_format($row->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">No revenue data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
