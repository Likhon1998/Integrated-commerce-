<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Balance & Profit</h2>
            <p class="text-sm text-gray-500 mt-1">Revenue minus cost of goods — your gross profit balance.</p>
        </div>

        @include('analytics.partials.toolbar')

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="bg-indigo-600 text-white rounded-2xl p-5">
                <p class="text-[10px] font-bold text-indigo-200 uppercase">Revenue</p>
                <p class="text-2xl font-black mt-2">৳{{ number_format($revenue, 2) }}</p>
            </div>
            <div class="bg-rose-600 text-white rounded-2xl p-5">
                <p class="text-[10px] font-bold text-rose-200 uppercase">Expense (COGS)</p>
                <p class="text-2xl font-black mt-2">৳{{ number_format($expense, 2) }}</p>
            </div>
            <div class="bg-emerald-600 text-white rounded-2xl p-5">
                <p class="text-[10px] font-bold text-emerald-200 uppercase">Gross Profit</p>
                <p class="text-2xl font-black mt-2">৳{{ number_format($profit, 2) }}</p>
            </div>
            <div class="bg-slate-900 text-white rounded-2xl p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Profit Margin</p>
                <p class="text-2xl font-black mt-2">{{ number_format($margin, 1) }}%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold mb-4">POS Channel Balance</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Revenue</span><span class="font-bold text-indigo-600">৳{{ number_format($posRevenue, 2) }}</span></div>
                    <div class="flex justify-between"><span>COGS</span><span class="font-bold text-rose-600">৳{{ number_format($posCogs, 2) }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span class="font-bold">Profit</span><span class="font-black text-emerald-600">৳{{ number_format($posRevenue - $posCogs, 2) }}</span></div>
                </div>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <h3 class="font-bold mb-4">Web Store Balance</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Revenue</span><span class="font-bold text-emerald-600">৳{{ number_format($webRevenue, 2) }}</span></div>
                    <div class="flex justify-between"><span>COGS</span><span class="font-bold text-rose-600">৳{{ number_format($webCogs, 2) }}</span></div>
                    <div class="flex justify-between border-t pt-2"><span class="font-bold">Profit</span><span class="font-black text-emerald-600">৳{{ number_format($webRevenue - $webCogs, 2) }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
