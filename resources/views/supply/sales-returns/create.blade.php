<x-supply-layout title="New Sales Return" subtitle="Customer returns goods from a completed sale. Stock goes back to POS & web; accounts reverse the sale.">
    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-600">
        <p class="font-semibold text-gray-800 mb-1">How it works</p>
        <ol class="list-decimal list-inside space-y-1 text-xs sm:text-sm">
            <li>Choose a <span class="font-semibold">completed</span> order (POS or website)</li>
            <li>Enter return qty for items (partial returns allowed)</li>
            <li>Stock ↑ · Revenue ↓ · Inventory asset ↑</li>
        </ol>
    </div>

    <form method="GET" class="mb-5">
        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Select order</label>
        <select name="order_id" class="w-full text-sm rounded-lg border-gray-200 py-1.5" onchange="this.form.submit()">
            <option value="">Choose invoice…</option>
            @foreach($orders as $order)
                <option value="{{ $order->id }}" @selected(optional($selectedOrder)->id === $order->id)>
                    {{ $order->invoice_no }} — ৳{{ number_format($order->total_amount, 2) }} — {{ $order->created_at->format('M d, Y') }}
                </option>
            @endforeach
        </select>
        @if($orders->isEmpty())
            <p class="mt-2 text-xs text-amber-700">No completed orders found. Complete a sale first.</p>
        @endif
    </form>

    @if($selectedOrder)
        @php $alreadyReturned = $alreadyReturned ?? []; @endphp
        <form method="POST" action="{{ route('supply.sales-returns.store') }}"
              class="bg-white rounded-xl border border-gray-200 overflow-hidden"
              x-data="{
                  rows: @js($selectedOrder->items->map(function ($item) use ($alreadyReturned) {
                      $returned = (int) ($alreadyReturned[$item->id] ?? 0);
                      return [
                          'order_item_id' => $item->id,
                          'product_id' => $item->product_id,
                          'name' => $item->product->name ?? 'Product',
                          'sold' => (int) $item->quantity,
                          'returned' => $returned,
                          'remaining' => max(0, (int) $item->quantity - $returned),
                          'unit_price' => (float) $item->unit_price,
                          'quantity' => 0,
                          'refund_amount' => 0,
                      ];
                  })->values()),
                  syncRefund(row) {
                      row.refund_amount = Math.round((Number(row.quantity) || 0) * (Number(row.unit_price) || 0) * 100) / 100;
                  },
                  totalRefund() {
                      return this.rows.reduce((s, r) => s + (Number(r.refund_amount) || 0), 0);
                  }
              }">
            @csrf
            <input type="hidden" name="order_id" value="{{ $selectedOrder->id }}">

            <div class="px-4 py-3 border-b bg-slate-50 text-sm">
                Invoice <span class="font-bold">{{ $selectedOrder->invoice_no }}</span>
                · Total ৳{{ number_format($selectedOrder->total_amount, 2) }}
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-wider text-gray-400 border-b">
                            <th class="text-left font-semibold px-4 py-2">Product</th>
                            <th class="font-semibold px-2 py-2">Sold</th>
                            <th class="font-semibold px-2 py-2">Already returned</th>
                            <th class="font-semibold px-2 py-2">Return qty</th>
                            <th class="font-semibold px-2 py-2">Refund ৳</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="row.order_item_id">
                            <tr class="border-b border-gray-50" :class="row.remaining < 1 ? 'opacity-50' : ''">
                                <td class="px-4 py-2 font-semibold">
                                    <span x-text="row.name"></span>
                                    <input type="hidden" :name="'items['+index+'][order_item_id]'" :value="row.order_item_id">
                                    <input type="hidden" :name="'items['+index+'][product_id]'" :value="row.product_id">
                                </td>
                                <td class="px-2 py-2 text-center" x-text="row.sold"></td>
                                <td class="px-2 py-2 text-center" x-text="row.returned"></td>
                                <td class="px-2 py-2">
                                    <input type="number"
                                           :name="'items['+index+'][quantity]'"
                                           x-model.number="row.quantity"
                                           @input="syncRefund(row)"
                                           min="0"
                                           :max="row.remaining"
                                           :disabled="row.remaining < 1"
                                           class="w-20 text-sm text-center rounded-lg border-gray-200 py-1 mx-auto block">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number"
                                           step="0.01"
                                           :name="'items['+index+'][refund_amount]'"
                                           x-model.number="row.refund_amount"
                                           min="0"
                                           :disabled="row.remaining < 1"
                                           class="w-24 text-sm text-center rounded-lg border-gray-200 py-1 mx-auto block">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td colspan="4" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Refund total</td>
                            <td class="px-2 py-3 text-center text-sm font-bold" x-text="'৳' + totalRefund().toFixed(2)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-4 border-t space-y-3">
                <input type="text" name="notes" placeholder="Notes (optional)" class="w-full text-sm rounded-lg border-gray-200 py-1.5 placeholder:text-gray-400">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-5 py-2 rounded-lg font-bold">Process return</button>
            </div>
        </form>
    @endif
</x-supply-layout>
