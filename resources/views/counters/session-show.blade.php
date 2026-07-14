<x-app-layout>
    <div class="max-w-3xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 mt-4">
            <a href="{{ route('counters.sessions.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Back to sessions</a>
            <h2 class="text-2xl font-black text-gray-950 tracking-tight mt-2">
                {{ $session->counter->name ?? 'Counter' }} session
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $session->opened_at->format('M d, Y H:i') }}
                @if($session->closed_at)
                    → {{ $session->closed_at->format('H:i') }}
                @endif
                · <span class="uppercase font-bold text-xs">{{ $session->status }}</span>
            </p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid sm:grid-cols-2 gap-3 mb-6">
            <div class="bg-white rounded-xl border p-4">
                <p class="text-[11px] font-bold uppercase text-gray-400">Starting cash</p>
                <p class="text-xl font-black">৳{{ number_format($session->opening_cash, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">Opened by {{ $session->opener->name ?? '—' }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-[11px] font-bold uppercase text-gray-400">Closing cash</p>
                <p class="text-xl font-black">{{ $session->closing_cash !== null ? '৳'.number_format($session->closing_cash, 2) : '—' }}</p>
                <p class="text-xs text-gray-500 mt-1">Closed by {{ $session->closer->name ?? '—' }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-[11px] font-bold uppercase text-gray-400">Total sales</p>
                <p class="text-xl font-black">৳{{ number_format($session->total_sales, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $session->order_count }} completed orders</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-[11px] font-bold uppercase text-gray-400">Variance</p>
                <p class="text-xl font-black {{ ($session->variance ?? 0) < 0 ? 'text-red-600' : (($session->variance ?? 0) > 0 ? 'text-emerald-600' : '') }}">
                    {{ $session->variance !== null ? '৳'.number_format($session->variance, 2) : '—' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Counted − expected (৳{{ number_format($session->expected_cash ?? 0, 2) }})</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-4 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">Cash sales</span><span class="font-semibold">৳{{ number_format($session->cash_sales, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Card sales</span><span class="font-semibold">৳{{ number_format($session->card_sales, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Mobile / bKash</span><span class="font-semibold">৳{{ number_format($session->mobile_sales, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Cash refunds</span><span class="font-semibold text-red-600">৳{{ number_format($session->cash_refunds, 2) }}</span></div>
            @if($session->notes)
                <div class="pt-3 border-t text-xs text-gray-600 whitespace-pre-line">{{ $session->notes }}</div>
            @endif
        </div>

        @if($session->status === 'open')
            <div class="mt-5">
                <a href="{{ route('counters.sessions.close-form', $session) }}" class="inline-flex bg-slate-900 text-white text-sm font-bold px-4 py-2 rounded-lg">Close session</a>
            </div>
        @endif
    </div>
</x-app-layout>
