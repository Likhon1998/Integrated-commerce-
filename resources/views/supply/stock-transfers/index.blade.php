<x-supply-layout title="Stock Transfer" subtitle="Move stock between store and warehouse locations." :action-url="route('supply.stock-transfers.create')" action-label="+ New Transfer">
    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Transfer #</th><th class="p-4">From</th><th class="p-4">To</th><th class="p-4">Status</th><th class="p-4">Date</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($transfers as $transfer)
                    <tr>
                        <td class="p-4 font-bold">{{ $transfer->transfer_number }}</td>
                        <td class="p-4">{{ $transfer->fromLocation->name }}</td>
                        <td class="p-4">{{ $transfer->toLocation->name }}</td>
                        <td class="p-4 uppercase text-xs font-bold">{{ $transfer->status }}</td>
                        <td class="p-4">{{ $transfer->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400">No transfers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $transfers->links() }}</div>
    </div>
</x-supply-layout>
