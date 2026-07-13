<x-supply-layout title="Sales Returns" subtitle="Process customer returns and restore sellable stock." :action-url="route('supply.sales-returns.create')" action-label="+ New Sales Return">
    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Return #</th><th class="p-4">Invoice</th><th class="p-4">Refund</th><th class="p-4">Date</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($returns as $return)
                    <tr>
                        <td class="p-4 font-bold">{{ $return->return_number }}</td>
                        <td class="p-4">{{ $return->order->invoice_no ?? '—' }}</td>
                        <td class="p-4">৳{{ number_format($return->total_refund, 2) }}</td>
                        <td class="p-4">{{ $return->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-10 text-center text-gray-400">No sales returns yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $returns->links() }}</div>
    </div>
</x-supply-layout>
