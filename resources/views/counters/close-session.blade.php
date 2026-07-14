<x-app-layout>
    <div class="max-w-2xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 mt-4">
            <a href="{{ route('counters.sessions.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Back</a>
            <h2 class="text-2xl font-black text-gray-950 tracking-tight mt-2">Close {{ $session->counter->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">Count the cash in the drawer and close the day’s session.</p>
        </div>

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-5 grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Opened</p>
                <p class="font-semibold">{{ $session->opened_at->format('M d, Y H:i') }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Starting cash</p>
                <p class="font-semibold">৳{{ number_format($session->opening_cash, 2) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Orders</p>
                <p class="font-semibold">{{ $stats['order_count'] }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Total sales</p>
                <p class="font-semibold">৳{{ number_format($stats['total_sales'], 2) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Cash sales</p>
                <p class="font-semibold">৳{{ number_format($stats['cash_sales'], 2) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Card / Mobile</p>
                <p class="font-semibold">৳{{ number_format($stats['card_sales'] + $stats['mobile_sales'], 2) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Cash refunds</p>
                <p class="font-semibold text-red-600">৳{{ number_format($stats['cash_refunds'], 2) }}</p>
            </div>
            <div>
                <p class="text-[11px] uppercase font-bold text-gray-400">Expected in drawer</p>
                <p class="font-black text-emerald-700 text-lg">৳{{ number_format($expected, 2) }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Start + cash sales − cash refunds</p>
            </div>
        </div>

        <form method="POST" action="{{ route('counters.sessions.close', $session) }}" class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Counted closing cash (৳)</label>
                <input type="number" step="0.01" min="0" name="closing_cash" value="{{ $expected }}"
                       class="w-full text-sm rounded-lg border-gray-200 py-2 font-bold" required>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">Closing notes</label>
                <input type="text" name="notes" placeholder="Optional — shortage reason, etc."
                       class="w-full text-sm rounded-lg border-gray-200 py-1.5 placeholder:text-gray-400">
            </div>
            <button class="w-full bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-2.5 rounded-lg">
                Close counter session
            </button>
        </form>
    </div>
</x-app-layout>
