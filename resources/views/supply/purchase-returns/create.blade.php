<x-supply-layout title="New Purchase Return" subtitle="Stock will be deducted from POS and web store.">
    <form method="POST" action="{{ route('supply.purchase-returns.store') }}" class="space-y-6" x-data="{ rows: [0] }">
        @csrf
        <div class="bg-white rounded-2xl border p-6 grid md:grid-cols-2 gap-4">
            <div><label class="text-xs font-bold text-gray-500 uppercase">Supplier</label><select name="supplier_id" class="w-full rounded-xl border-gray-200 mt-1" required>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold text-gray-500 uppercase">Linked PO (optional)</label><select name="purchase_order_id" class="w-full rounded-xl border-gray-200 mt-1"><option value="">—</option>@foreach($purchaseOrders as $po)<option value="{{ $po->id }}">{{ $po->po_number }}</option>@endforeach</select></div>
            <div class="md:col-span-2"><label class="text-xs font-bold text-gray-500 uppercase">Notes</label><textarea name="notes" class="w-full rounded-xl border-gray-200 mt-1" rows="2"></textarea></div>
        </div>
        <div class="bg-white rounded-2xl border p-6">
            <div class="flex justify-between mb-4"><h3 class="font-bold">Return Items</h3><button type="button" @click="rows.push(Date.now())" class="text-indigo-600 font-bold text-sm">+ Add Row</button></div>
            <template x-for="(row, index) in rows" :key="row">
                <div class="grid md:grid-cols-4 gap-3 mb-3">
                    <select :name="'items['+index+'][product_id]'" class="rounded-xl border-gray-200 md:col-span-2" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->stock_quantity }} in stock)</option>@endforeach</select>
                    <input type="number" :name="'items['+index+'][quantity]'" placeholder="Qty" min="1" class="rounded-xl border-gray-200" required>
                    <input type="number" step="0.01" :name="'items['+index+'][unit_cost]'" placeholder="Unit cost" min="0" class="rounded-xl border-gray-200" required>
                </div>
            </template>
        </div>
        <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold">Submit Return</button>
    </form>
</x-supply-layout>
