<x-supply-layout :title="'PO ' . $order->po_number" subtitle="Receive items to sync stock with POS and web store.">
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-gray-400 uppercase font-bold">Supplier</p><p class="font-bold">{{ $order->supplier->name }}</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-gray-400 uppercase font-bold">Status</p><p class="font-bold uppercase">{{ $order->status }}</p></div>
        <div class="bg-white rounded-2xl border p-4"><p class="text-xs text-gray-400 uppercase font-bold">Total</p><p class="font-bold">৳{{ number_format($order->total_amount, 2) }}</p></div>
    </div>
    @if($order->status !== 'received')
    <form method="POST" action="{{ route('supply.purchase-orders.receive', $order) }}" class="bg-white rounded-2xl border overflow-hidden">
        @csrf
        <table class="w-full text-sm">
            <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                <tr><th class="p-4 text-left">Product</th><th class="p-4">Ordered</th><th class="p-4">Received</th><th class="p-4">Receive Now</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($order->items as $i => $item)
                    <tr>
                        <td class="p-4 font-semibold">{{ $item->product->name }}<input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}"></td>
                        <td class="p-4 text-center">{{ $item->quantity }}</td>
                        <td class="p-4 text-center">{{ $item->received_quantity }}</td>
                        <td class="p-4"><input type="number" name="items[{{ $i }}][receive_qty]" value="{{ max(0, $item->quantity - $item->received_quantity) }}" min="0" max="{{ $item->quantity - $item->received_quantity }}" class="w-24 rounded-lg border-gray-200 mx-auto block text-center"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t"><button class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold">Receive Stock</button></div>
    </form>
    @else
        <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-800 font-semibold">This purchase order is fully received.</div>
    @endif
</x-supply-layout>
