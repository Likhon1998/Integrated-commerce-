<x-supply-layout title="Stock Adjustment" subtitle="Manual corrections after opening stock is set. Full movement history below (sales are recorded separately in POS / online checkout).">
    <form method="POST" action="{{ route('supply.adjustments.store') }}" class="bg-white rounded-2xl border p-6 mb-8 grid md:grid-cols-4 gap-4 max-w-4xl">
        @csrf
        <div class="md:col-span-2"><label class="text-xs font-bold text-gray-500 uppercase">Product</label><select name="product_id" class="w-full rounded-xl border-gray-200 mt-1" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->stock_quantity }})</option>@endforeach</select></div>
        <div><label class="text-xs font-bold text-gray-500 uppercase">Type</label><select name="type" class="w-full rounded-xl border-gray-200 mt-1"><option value="in">Stock In</option><option value="out">Stock Out</option></select></div>
        <div><label class="text-xs font-bold text-gray-500 uppercase">Quantity</label><input type="number" name="quantity" min="1" class="w-full rounded-xl border-gray-200 mt-1" required></div>
        <div class="md:col-span-4"><label class="text-xs font-bold text-gray-500 uppercase">Reason / Reference</label><input name="reference" class="w-full rounded-xl border-gray-200 mt-1" placeholder="e.g. Physical count correction" required></div>
        <div class="md:col-span-4"><button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold">Save Adjustment</button></div>
    </form>
    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Date</th><th class="p-4">Product</th><th class="p-4">Type</th><th class="p-4">Reason</th><th class="p-4">Qty</th><th class="p-4">Reference</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($movements as $m)
                    <tr>
                        <td class="p-4">{{ $m->created_at->format('M d, Y H:i') }}</td>
                        <td class="p-4 font-semibold">{{ $m->product->name ?? '—' }}</td>
                        <td class="p-4 uppercase font-bold">{{ $m->type }}</td>
                        <td class="p-4">{{ str_replace('_', ' ', $m->reason ?? '—') }}</td>
                        <td class="p-4">{{ $m->quantity }}</td>
                        <td class="p-4 text-gray-500">{{ $m->reference }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center text-gray-400">No adjustments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $movements->links() }}</div>
    </div>
</x-supply-layout>
