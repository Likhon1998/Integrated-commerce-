<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Orders Analytics</h2>
            <p class="text-sm text-gray-500 mt-1">Track POS counter sales and website orders together.</p>
        </div>

        @include('analytics.partials.toolbar')

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach([
                ['Total Orders', $summary['total'], 'text-gray-900'],
                ['POS Orders', $summary['pos'], 'text-indigo-600'],
                ['Web Orders', $summary['web'], 'text-emerald-600'],
                ['Pending', $summary['pending'], 'text-amber-600'],
                ['Completed', $summary['completed'], 'text-slate-600'],
            ] as [$label, $value, $color])
                <div class="bg-white border rounded-2xl p-4 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase">{{ $label }}</p>
                    <p class="text-2xl font-black {{ $color }} mt-2">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b"><h3 class="font-bold">Recent Orders</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Invoice</th>
                            <th class="px-5 py-3 text-left">Channel</th>
                            <th class="px-5 py-3 text-left">Customer</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr class="border-t border-gray-50">
                                <td class="px-5 py-3 font-mono text-xs">{{ $order->invoice_no }}</td>
                                <td class="px-5 py-3">
                                    @if($order->counter_id)
                                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded text-xs font-bold">POS</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-xs font-bold">Web</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $order->customer->name ?? 'Walk-in' }}</td>
                                <td class="px-5 py-3 capitalize">{{ $order->status }}</td>
                                <td class="px-5 py-3 text-right font-bold">৳{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">No orders in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $recentOrders->links() }}</div>
        </div>
    </div>
</x-app-layout>
