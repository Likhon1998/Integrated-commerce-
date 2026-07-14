<x-supply-layout :title="'PO ' . $order->po_number" :subtitle="'Supplier: ' . ($order->supplier->name ?? '—') . ' · Stock syncs to POS & web on receive.'">
    <div class="grid md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border p-4">
            <p class="text-xs text-gray-400 uppercase font-bold">Supplier</p>
            <p class="font-bold">{{ $order->supplier->name }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-4">
            <p class="text-xs text-gray-400 uppercase font-bold">Status</p>
            <p class="font-bold uppercase tracking-wide
                @if($order->status === 'received') text-emerald-600
                @elseif($order->status === 'partial') text-amber-600
                @elseif($order->status === 'cancelled') text-red-600
                @else text-indigo-600 @endif">{{ $order->status }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-4">
            <p class="text-xs text-gray-400 uppercase font-bold">PO Total</p>
            <p class="font-bold">৳{{ number_format($order->total_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-4">
            <p class="text-xs text-gray-400 uppercase font-bold">AP Balance (shop)</p>
            <p class="font-bold">৳{{ number_format($apBalance ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        @if($order->status !== 'cancelled' && $order->items->every(fn ($i) => $i->received_quantity === 0))
            <a href="{{ route('supply.purchase-orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-bold text-gray-700 hover:bg-gray-50">Edit PO</a>
            <form method="POST" action="{{ route('supply.purchase-orders.cancel', $order) }}" onsubmit="return confirm('Cancel this purchase order?')">
                @csrf
                <button class="inline-flex items-center px-4 py-2 rounded-xl bg-red-50 border border-red-100 text-sm font-bold text-red-700 hover:bg-red-100">Cancel PO</button>
            </form>
        @endif
        <a href="{{ route('supply.purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-bold text-gray-700 hover:bg-gray-50">Back to list</a>
    </div>

    @if(!in_array($order->status, ['received', 'cancelled'], true))
        <form method="POST" action="{{ route('supply.purchase-orders.receive', $order) }}" class="bg-white rounded-2xl border overflow-hidden mb-8">
            @csrf
            <div class="px-5 py-4 border-b bg-slate-50">
                <h3 class="font-bold text-gray-900">Receive stock</h3>
                <p class="text-xs text-gray-500 mt-1">Increases sellable stock (POS + web) · Dr Inventory · Cr Accounts Payable</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                    <tr>
                        <th class="p-4 text-left">Product</th>
                        <th class="p-4">Unit cost</th>
                        <th class="p-4">Ordered</th>
                        <th class="p-4">Received</th>
                        <th class="p-4">Receive now</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($order->items as $i => $item)
                        @php $remaining = max(0, $item->quantity - $item->received_quantity); @endphp
                        <tr>
                            <td class="p-4 font-semibold">
                                {{ $item->product->name }}
                                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                            </td>
                            <td class="p-4 text-center">৳{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="p-4 text-center">{{ $item->quantity }}</td>
                            <td class="p-4 text-center">{{ $item->received_quantity }}</td>
                            <td class="p-4">
                                <input type="number"
                                       name="items[{{ $i }}][receive_qty]"
                                       value="{{ $remaining }}"
                                       min="0"
                                       max="{{ $remaining }}"
                                       @disabled($remaining < 1)
                                       class="w-24 rounded-lg border-gray-200 mx-auto block text-center">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t">
                <button class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-emerald-700">Receive Stock</button>
            </div>
        </form>
    @elseif($order->status === 'received')
        <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-800 font-semibold mb-8">
            Fully received. Stock is available on POS and the online store.
        </div>
    @elseif($order->status === 'cancelled')
        <div class="p-6 bg-red-50 border border-red-100 rounded-2xl text-red-800 font-semibold mb-8">
            This purchase order was cancelled. No stock was received.
        </div>
    @endif

    @if(in_array($order->status, ['partial', 'received'], true) && ($cashAccounts?->count() ?? 0) > 0)
        <form method="POST" action="{{ route('supply.purchase-orders.pay', $order) }}" class="bg-white rounded-2xl border p-6 mb-8 max-w-2xl space-y-4">
            @csrf
            <div>
                <h3 class="font-bold text-gray-900">Pay supplier</h3>
                <p class="text-xs text-gray-500 mt-1">Clears Accounts Payable (Dr AP · Cr Cash / Wallet)</p>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ $order->total_amount }}" class="w-full rounded-xl border-gray-200 mt-1" required>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Pay from</label>
                    <select name="account_id" class="w-full rounded-xl border-gray-200 mt-1" required>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-bold text-gray-500 uppercase">Notes</label>
                <input type="text" name="notes" class="w-full rounded-xl border-gray-200 mt-1" placeholder="Optional reference">
            </div>
            <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700">Record Payment</button>
        </form>
    @endif

    @if(($ledgerEntries?->count() ?? 0) > 0)
        <div class="bg-white rounded-2xl border overflow-hidden">
            <div class="px-5 py-4 border-b bg-slate-50">
                <h3 class="font-bold text-gray-900">Accounting entries for this PO</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-900 text-white text-[11px] uppercase tracking-widest">
                    <tr>
                        <th class="p-4 text-left">Txn #</th>
                        <th class="p-4 text-left">Date</th>
                        <th class="p-4 text-left">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($ledgerEntries as $txn)
                        <tr>
                            <td class="p-4 font-mono text-xs">{{ $txn->transaction_no }}</td>
                            <td class="p-4">{{ \Carbon\Carbon::parse($txn->transaction_date)->format('M d, Y') }}</td>
                            <td class="p-4">{{ $txn->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-supply-layout>
