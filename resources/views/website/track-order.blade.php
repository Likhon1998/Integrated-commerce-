@extends('website.layout')

@section('title', 'Track Order — ' . ($settings->store_name ?? 'Store'))

@section('content')
@php $currency = $settings->currency_symbol ?? 'Tk'; @endphp
<div class="max-w-2xl mx-auto px-4 py-12" x-data="trackOrder(@json(auth()->check() && auth()->user()->isStorefrontCustomer()), @json($prefillInvoice ?? ''))">
    <h1 class="text-3xl font-extrabold text-center mb-2">Where is my order?</h1>
    <p class="text-gray-600 text-center mb-8">See live status — from store to your doorstep.</p>

    <div class="bg-white border rounded-2xl p-6 space-y-4 shadow-sm">
        <input x-model="invoice" type="text" placeholder="Invoice number (e.g. WEB-1-2026-12345)" class="w-full border rounded-xl px-4 py-3 text-sm">
        <input x-show="!loggedIn" x-model="phone" type="text" placeholder="Phone number used at checkout" class="w-full border rounded-xl px-4 py-3 text-sm">
        <p x-show="loggedIn" class="text-xs text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2">Signed in — we will match orders on your account automatically.</p>
        <button @click="track(true)" :disabled="loading" class="w-full gaget-btn-primary py-3 disabled:opacity-50">
            <span x-text="loading ? 'Checking...' : 'Track my order'"></span>
        </button>
        <p class="text-xs text-slate-500 text-center">Tracking refreshes automatically every 20 seconds after an order is found.</p>
    </div>

    <div x-show="result" x-cloak class="mt-6 bg-white border rounded-2xl p-6 shadow-sm">
        <template x-if="!success">
            <p class="text-rose-600 font-medium text-center" x-text="message"></p>
        </template>

        <template x-if="success">
            <div class="space-y-6">
                <div class="text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Invoice</p>
                    <p class="text-lg font-bold text-slate-900" x-text="invoiceNo"></p>
                    <p class="mt-2 text-sm text-blue-700 font-semibold" x-text="whereIsProduct"></p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-400 mb-3">Delivery progress</p>
                    <div class="space-y-4">
                        <template x-for="(step, idx) in timeline" :key="step.key">
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold"
                                         :class="step.done ? (step.active ? 'bg-blue-600 text-white' : 'bg-emerald-500 text-white') : 'bg-slate-200 text-slate-500'"
                                         x-text="step.done ? '✓' : (idx + 1)"></div>
                                    <div x-show="idx < timeline.length - 1" class="w-px flex-1 min-h-[20px] bg-slate-200 mt-1"></div>
                                </div>
                                <div class="flex-1 pb-2">
                                    <p class="font-semibold text-sm" :class="step.active ? 'text-blue-700' : 'text-slate-800'" x-text="step.label"></p>
                                    <p x-show="step.at" class="text-xs text-slate-500 mt-0.5" x-text="step.at"></p>
                                    <p x-show="step.note" class="text-xs text-slate-600 mt-1" x-text="step.note"></p>
                                    <p x-show="step.courier" class="text-xs font-semibold text-indigo-700 mt-1">
                                        <span x-text="step.courier"></span>
                                        <span x-show="step.tracking"> · Tracking: <span x-text="step.tracking"></span></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400 mb-2">Items in this order</p>
                    <div class="space-y-2">
                        <template x-for="item in items" :key="item.name + item.qty">
                            <div class="flex justify-between text-sm border rounded-lg px-3 py-2">
                                <span x-text="item.qty + ' × ' + item.name"></span>
                                <span class="font-semibold" x-text="@json($currency) + item.subtotal"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-xs text-slate-400">Ordered</p>
                        <p class="font-semibold" x-text="date"></p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3 text-right">
                        <p class="text-xs text-slate-400">Total</p>
                        <p class="font-semibold" x-text="@json($currency) + total"></p>
                    </div>
                </div>
                <p x-show="deliveryAddress" class="text-xs text-slate-500">Deliver to: <span class="font-medium text-slate-700" x-text="deliveryAddress"></span></p>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function trackOrder(loggedIn, prefillInvoice) {
    return {
        loggedIn: loggedIn,
        invoice: prefillInvoice || '',
        phone: '',
        loading: false,
        result: false,
        success: false,
        pollHandle: null,
        message: '',
        invoiceNo: '',
        status: '',
        date: '',
        total: '',
        deliveryAddress: '',
        whereIsProduct: '',
        timeline: [],
        items: [],
        async track(manual = false) {
            if (!this.invoice) {
                this.result = true;
                this.success = false;
                this.message = 'Please enter your invoice number.';
                return;
            }
            this.loading = true;
            this.result = false;
            try {
                const body = { invoice_no: this.invoice };
                if (!this.loggedIn) body.phone = this.phone;
                const res = await fetch(@json(route('website.track.submit')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                this.result = true;
                this.success = !!data.success;
                this.message = data.message || '';
                if (data.success) {
                    this.invoiceNo = data.invoice;
                    this.status = data.status_label;
                    this.date = data.date;
                    this.total = data.total;
                    this.deliveryAddress = data.delivery_address || '';
                    this.whereIsProduct = data.where_is_product || '';
                    this.timeline = data.timeline || [];
                    this.items = data.items || [];
                    this.ensurePolling();
                } else if (manual) {
                    this.stopPolling();
                }
            } catch (e) {
                this.result = true;
                this.success = false;
                this.message = 'Network error. Please try again.';
            }
            this.loading = false;
        },
        ensurePolling() {
            if (this.pollHandle) return;
            this.pollHandle = setInterval(() => {
                if (document.visibilityState === 'visible' && this.invoice) {
                    this.track(false);
                }
            }, 20000);
        },
        stopPolling() {
            if (this.pollHandle) {
                clearInterval(this.pollHandle);
                this.pollHandle = null;
            }
        },
        init() {
            if (this.invoice) this.track();
            window.addEventListener('beforeunload', () => this.stopPolling());
        },
    };
}
</script>
@endpush
