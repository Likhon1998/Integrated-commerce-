<x-supply-layout title="Damage Product" subtitle="Write off damaged or expired stock from sellable inventory.">
    <form method="POST" action="{{ route('supply.damage-products.store') }}" class="bg-white rounded-2xl border p-6 mb-8 grid md:grid-cols-3 gap-4 max-w-3xl">
        @csrf
        <div class="md:col-span-2"><label class="text-xs font-bold text-gray-500 uppercase">Product</label><select name="product_id" class="w-full rounded-xl border-gray-200 mt-1" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->stock_quantity }} in stock)</option>@endforeach</select></div>
        <div><label class="text-xs font-bold text-gray-500 uppercase">Damaged Qty</label><input type="number" name="quantity" min="1" class="w-full rounded-xl border-gray-200 mt-1" required></div>
        <div class="md:col-span-3"><label class="text-xs font-bold text-gray-500 uppercase">Damage Reason</label><input name="reference" class="w-full rounded-xl border-gray-200 mt-1" placeholder="e.g. Broken packaging, water damage" required></div>
        <div class="md:col-span-3"><button class="bg-red-600 text-white px-6 py-2.5 rounded-xl font-bold">Write Off Stock</button></div>
    </form>
    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Date</th><th class="p-4">Product</th><th class="p-4">Qty</th><th class="p-4">Reason</th><th class="p-4">By</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($damages as $d)
                    <tr>
                        <td class="p-4">{{ $d->created_at->format('M d, Y') }}</td>
                        <td class="p-4 font-semibold">{{ $d->product->name ?? '—' }}</td>
                        <td class="p-4">{{ $d->quantity }}</td>
                        <td class="p-4">{{ $d->reference }}</td>
                        <td class="p-4">{{ $d->user->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400">No damage records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $damages->links() }}</div>
    </div>
</x-supply-layout>
