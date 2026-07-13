<x-supply-layout title="New Stock Transfer" subtitle="Warehouse → Store transfers increase sellable stock on web & POS.">
    <form method="POST" action="{{ route('supply.stock-transfers.store') }}" class="space-y-6" x-data="{ rows: [0] }">
        @csrf
        <div class="bg-white rounded-2xl border p-6 grid md:grid-cols-2 gap-4">
            <div><label class="text-xs font-bold text-gray-500 uppercase">From</label><select name="from_location_id" class="w-full rounded-xl border-gray-200 mt-1" required>@foreach($locations as $l)<option value="{{ $l->id }}">{{ ucfirst($l->type) }}: {{ $l->name }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold text-gray-500 uppercase">To</label><select name="to_location_id" class="w-full rounded-xl border-gray-200 mt-1" required>@foreach($locations as $l)<option value="{{ $l->id }}">{{ ucfirst($l->type) }}: {{ $l->name }}</option>@endforeach</select></div>
            <div class="md:col-span-2"><label class="text-xs font-bold text-gray-500 uppercase">Notes</label><textarea name="notes" class="w-full rounded-xl border-gray-200 mt-1" rows="2"></textarea></div>
        </div>
        <div class="bg-white rounded-2xl border p-6">
            <div class="flex justify-between mb-4"><h3 class="font-bold">Products</h3><button type="button" @click="rows.push(Date.now())" class="text-indigo-600 font-bold text-sm">+ Add Row</button></div>
            <template x-for="(row, index) in rows" :key="row">
                <div class="grid md:grid-cols-2 gap-3 mb-3">
                    <select :name="'items['+index+'][product_id]'" class="rounded-xl border-gray-200" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
                    <input type="number" :name="'items['+index+'][quantity]'" placeholder="Quantity" min="1" class="rounded-xl border-gray-200" required>
                </div>
            </template>
        </div>
        <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold">Complete Transfer</button>
    </form>
</x-supply-layout>
