<x-app-layout>
@php
    $statusColors = [
        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
        'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
        'shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-orange-100 text-orange-800 border-orange-200',
        'returned' => 'bg-rose-100 text-rose-800 border-rose-200',
        'refunded' => 'bg-rose-100 text-rose-800 border-rose-200',
    ];
    $statusClass = $statusColors[$order->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
@endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('online-orders.index') }}" class="text-[12px] font-semibold text-indigo-600 hover:text-indigo-700">← Back to online orders</a>
            <div class="mt-1.5 flex flex-wrap items-center gap-2.5">
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900">{{ $order->invoice_no }}</h1>
                <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-bold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <p class="mt-0.5 text-[12px] text-slate-500">Placed {{ $order->created_at->format('d M Y, h:i A') }} · {{ str_replace('_', ' ', ucfirst($order->payment_method)) }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="window.open('{{ route('pos.receipt', $order->id) }}', 'ReceiptWindow', 'width=400,height=620')"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-[12px] font-bold text-indigo-700 hover:bg-indigo-600 hover:text-white">
                Print receipt
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[13px] font-medium text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-[13px] font-medium text-rose-700">{{ session('error') }}</div>
    @endif

    {{-- Tracking progress --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-2">
            <h2 class="text-[14px] font-bold text-slate-900">Order tracking</h2>
            <p class="text-[11px] text-slate-500">Customer sees the same progress in My Account</p>
        </div>

        @if(in_array($order->status, ['cancelled', 'returned', 'refunded'], true))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] font-semibold text-rose-700">
                {{ $statusLabel }}
                @if($order->statusLogs->first()?->note)
                    <span class="font-normal text-rose-600"> — {{ $order->statusLogs->first()->note }}</span>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                @foreach($timeline as $step)
                    <div class="rounded-xl border px-3 py-3
                        @if($step['active']) border-indigo-300 bg-indigo-50
                        @elseif($step['done']) border-emerald-200 bg-emerald-50/70
                        @else border-slate-100 bg-slate-50 @endif">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold
                                @if($step['active']) bg-indigo-600 text-white
                                @elseif($step['done']) bg-emerald-500 text-white
                                @else bg-slate-200 text-slate-500 @endif">
                                @if($step['done'] && ! $step['active'])✓@else{{ $loop->iteration }}@endif
                            </span>
                            <p class="text-[12px] font-bold
                                @if($step['active']) text-indigo-800
                                @elseif($step['done']) text-emerald-800
                                @else text-slate-400 @endif">{{ $step['label'] }}</p>
                        </div>
                        @if($step['at'])
                            <p class="text-[10px] text-slate-500">{{ $step['at'] }}</p>
                        @else
                            <p class="text-[10px] text-slate-400">Waiting…</p>
                        @endif
                        @if(!empty($step['note']))
                            <p class="mt-1 line-clamp-2 text-[11px] text-slate-600">{{ $step['note'] }}</p>
                        @endif
                        @if(!empty($step['courier']))
                            <p class="mt-1 text-[11px] font-semibold text-indigo-700">{{ $step['courier'] }}@if(!empty($step['tracking'])) · {{ $step['tracking'] }}@endif</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_340px]">
        <div class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="text-[13px] font-bold text-slate-900">Customer & delivery</h3>
                    <dl class="mt-3 space-y-2 text-[13px]">
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Name</dt>
                            <dd class="font-semibold text-slate-900">{{ $order->customer->name ?? 'Guest' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Phone</dt>
                            <dd class="font-medium text-slate-700">{{ $order->customer->phone ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Address</dt>
                            <dd class="text-slate-600 leading-relaxed">{{ $order->customer->address ?? 'No address' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 class="text-[13px] font-bold text-slate-900">Payment summary</h3>
                    <dl class="mt-3 space-y-2 text-[13px]">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Items</dt>
                            <dd class="font-semibold text-slate-800">{{ $order->items->sum('quantity') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Subtotal</dt>
                            <dd class="font-semibold text-slate-800">Tk {{ number_format((float) $order->total_amount - (float) ($order->delivery_charge ?? 0), 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Delivery</dt>
                            <dd class="font-semibold text-slate-800">{{ ($order->delivery_charge ?? 0) > 0 ? 'Tk '.number_format((float) $order->delivery_charge, 2) : 'Free' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-slate-100 pt-2">
                            <dt class="font-bold text-slate-900">Total</dt>
                            <dd class="text-[15px] font-extrabold text-slate-900">Tk {{ number_format((float) $order->total_amount, 2) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Paid</dt>
                            <dd class="font-semibold text-slate-800">Tk {{ number_format((float) $order->paid_amount, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="text-[13px] font-bold text-slate-900">Items</h3>
                <div class="mt-3 divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <div class="flex items-start justify-between gap-4 py-2.5 text-[13px]">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">{{ $item->product->name ?? 'Unknown' }}</p>
                                <p class="text-[11px] text-slate-500">Qty {{ $item->quantity }} × Tk {{ number_format((float) ($item->unit_price ?? 0), 2) }}</p>
                            </div>
                            <p class="shrink-0 font-bold text-slate-900">Tk {{ number_format((float) $item->subtotal, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="text-[13px] font-bold text-slate-900">Status history</h3>
                <p class="mt-0.5 text-[11px] text-slate-500">Updates shown in the customer account</p>
                <div class="relative mt-4 space-y-0">
                    @forelse($order->statusLogs as $log)
                        <div class="relative flex gap-3 pb-4 last:pb-0">
                            @if(! $loop->last)
                                <span class="absolute left-[7px] top-4 bottom-0 w-px bg-slate-200"></span>
                            @endif
                            <span class="relative z-10 mt-1 h-3.5 w-3.5 shrink-0 rounded-full border-2
                                {{ $loop->first ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300 bg-white' }}"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <p class="text-[13px] font-bold text-slate-900">{{ $log->label }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $log->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                                @if($log->note)
                                    <p class="mt-0.5 text-[12px] text-slate-600">{{ $log->note }}</p>
                                @endif
                                @if($log->courier_name)
                                    <p class="mt-1 text-[11px] font-semibold text-indigo-700">
                                        {{ $log->courier_name }}
                                        @if($log->tracking_number)<span class="font-mono text-slate-500"> · {{ $log->tracking_number }}</span>@endif
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-[13px] text-slate-500">No status updates logged yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-4 xl:sticky xl:top-24 xl:self-start">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="text-[13px] font-bold text-slate-900">Update order status</h3>
                <p class="mt-0.5 text-[11px] text-slate-500">Saves instantly to the customer account</p>

                <form method="POST" action="{{ route('online-orders.update-status', $order) }}" class="mt-3 space-y-3" x-data="{ status: @js($order->status) }">
                    @csrf
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Status</label>
                        <select name="status" x-model="status" class="mt-1 w-full rounded-xl border-slate-200 text-[13px] font-semibold focus:border-indigo-400 focus:ring-indigo-400">
                            <option value="pending">Pending — Order received</option>
                            <option value="processing">Processing — Packing</option>
                            <option value="shipped">Shipped — Out for delivery</option>
                            <option value="completed">Completed — Delivered</option>
                            <option disabled>──────────</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="returned">Returned</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <div x-show="status === 'shipped'" x-cloak class="space-y-3 rounded-xl border border-indigo-100 bg-indigo-50/50 p-3">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Courier / delivery partner *</label>
                            <input type="text" name="courier_name" value="{{ old('courier_name', $order->shipping_courier) }}" placeholder="e.g. Pathao, Steadfast"
                                   class="mt-1 w-full rounded-xl border-slate-200 text-[13px] focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Tracking number</label>
                            <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->shipping_tracking_no) }}" placeholder="Courier tracking ID"
                                   class="mt-1 w-full rounded-xl border-slate-200 text-[13px] focus:border-indigo-400 focus:ring-indigo-400">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Note to customer</label>
                        <textarea name="customer_note" rows="2" placeholder="Optional message for the customer"
                                  class="mt-1 w-full rounded-xl border-slate-200 text-[13px] focus:border-indigo-400 focus:ring-indigo-400"></textarea>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-[13px] font-bold text-white hover:bg-indigo-700">
                        Save & notify customer
                    </button>
                </form>
            </div>

            @if($order->shipping_courier)
                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-indigo-500">Live shipment</p>
                    <p class="mt-1 text-[14px] font-bold text-indigo-950">{{ $order->shipping_courier }}</p>
                    @if($order->shipping_tracking_no)
                        <p class="mt-1 font-mono text-[12px] text-indigo-700">{{ $order->shipping_tracking_no }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
