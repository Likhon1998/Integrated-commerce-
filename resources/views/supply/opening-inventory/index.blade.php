<x-supply-layout title="Opening Inventory" subtitle="Set starting stock quantities — synced to POS terminal and online store.">
    <form method="POST" action="{{ route('supply.opening-inventory.store') }}" class="bg-white rounded-2xl border overflow-hidden">
        @csrf
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Product</th><th class="p-4">Current Stock</th><th class="p-4">Opening Qty</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($products as $i => $product)
                    <tr>
                        <td class="p-4 font-semibold">{{ $product->name }}<input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $product->id }}"></td>
                        <td class="p-4 text-center">{{ $product->stock_quantity }}</td>
                        <td class="p-4"><input type="number" name="items[{{ $i }}][quantity]" value="{{ $product->stock_quantity }}" min="0" class="w-28 rounded-lg border-gray-200 mx-auto block text-center"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t"><button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold">Save Opening Inventory</button></div>
    </form>
</x-supply-layout>
