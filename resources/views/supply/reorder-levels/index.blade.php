<x-supply-layout title="Reorder Level" subtitle="Set alert and reorder quantities — low stock appears on dashboard and reports.">
    @if($lowStock->count())
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm font-semibold">
            {{ $lowStock->count() }} product(s) at or below alert level need reordering.
        </div>
    @endif
    <form method="POST" action="{{ route('supply.reorder-levels.update') }}" class="bg-white rounded-2xl border overflow-hidden">
        @csrf @method('PUT')
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Product</th><th class="p-4">Stock</th><th class="p-4">Alert Level</th><th class="p-4">Reorder Qty</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($products as $i => $product)
                    <tr class="{{ $product->stock_quantity <= $product->alert_quantity ? 'bg-amber-50/50' : '' }}">
                        <td class="p-4 font-semibold">{{ $product->name }}<input type="hidden" name="products[{{ $i }}][id]" value="{{ $product->id }}"></td>
                        <td class="p-4 text-center font-bold">{{ $product->stock_quantity }}</td>
                        <td class="p-4"><input type="number" name="products[{{ $i }}][alert_quantity]" value="{{ $product->alert_quantity }}" min="0" class="w-24 rounded-lg border-gray-200 mx-auto block text-center"></td>
                        <td class="p-4"><input type="number" name="products[{{ $i }}][reorder_quantity]" value="{{ $product->reorder_quantity ?? 0 }}" min="0" class="w-24 rounded-lg border-gray-200 mx-auto block text-center"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t"><button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold">Update Reorder Levels</button></div>
    </form>
</x-supply-layout>
