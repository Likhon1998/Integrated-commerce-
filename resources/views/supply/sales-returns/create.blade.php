<x-supply-layout title="New Sales Return" subtitle="Select an order and return items — stock syncs to web store.">
    <form method="GET" class="mb-6 flex gap-3">
        <select name="order_id" class="rounded-xl border-gray-200 flex-1" onchange="this.form.submit()">
            <option value="">Select order...</option>
            @foreach($orders as $order)
                <option value="{{ $order->id }}" @selected(optional($selectedOrder)->id === $order->id)>{{ $order->invoice_no }} — ৳{{ number_format($order->total_amount, 2) }}</option>
            @endforeach
        </select>
    </form>
    @if($selectedOrder)
    <form method="POST" action="{{ route('supply.sales-returns.store') }}" class="bg-white rounded-2xl border overflow-hidden">
        @csrf
        <input type="hidden" name="order_id" value="{{ $selectedOrder->id }}">
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Product</th><th class="p-4">Sold Qty</th><th class="p-4">Return Qty</th><th class="p-4">Refund Amount</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($selectedOrder->items as $i => $item)
                    <tr>
                        <td class="p-4 font-semibold">{{ $item->product->name ?? 'Product' }}
                            <input type="hidden" name="items[{{ $i }}][order_item_id]" value="{{ $item->id }}">
                            <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                        </td>
                        <td class="p-4 text-center">{{ $item->quantity }}</td>
                        <td class="p-4"><input type="number" name="items[{{ $i }}][quantity]" value="0" min="0" max="{{ $item->quantity }}" class="w-24 rounded-lg border-gray-200 mx-auto block text-center"></td>
                        <td class="p-4"><input type="number" step="0.01" name="items[{{ $i }}][refund_amount]" value="0" min="0" class="w-28 rounded-lg border-gray-200 mx-auto block text-center"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t space-y-3">
            <textarea name="notes" placeholder="Notes (optional)" class="w-full rounded-xl border-gray-200" rows="2"></textarea>
            <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold">Process Return</button>
        </div>
    </form>
    @endif
</x-supply-layout>
