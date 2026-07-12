<x-app-layout>
    <div class="max-w-3xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Record small expenses from petty cash.</p>
        </div>

        @include('accounts.partials.toolbar')

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('error') }}</div>
        @endif

        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl px-5 py-4">
            <p class="text-[10px] font-bold text-indigo-400 uppercase">Petty Cash Available</p>
            <p class="text-3xl font-black text-indigo-600 mt-1">৳{{ number_format($pettyBalance, 2) }}</p>
        </div>

        <form action="{{ route('accounts.petty-cash.store') }}" method="POST" class="bg-white border rounded-2xl shadow-sm p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Amount (৳)</label>
                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full border-gray-200 rounded-lg text-sm px-3 py-2.5">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Description</label>
                <textarea name="description" rows="3" required class="w-full border-gray-200 rounded-lg text-sm px-3 py-2.5" placeholder="e.g. Stationery, courier, tea..."></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white text-sm font-bold py-3 rounded-xl hover:bg-indigo-700">Record Expense</button>
        </form>
    </div>
</x-app-layout>
