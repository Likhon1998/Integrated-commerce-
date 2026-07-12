<x-app-layout>
    <div class="max-w-3xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Move funds between counters or cash accounts.</p>
        </div>

        @include('accounts.partials.toolbar')

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-medium">{{ session('error') }}</div>
        @endif

        <form action="{{ route('accounts.transfer.store') }}" method="POST" class="bg-white border rounded-2xl shadow-sm p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">From Account</label>
                <select name="from_account_id" required class="w-full border-gray-200 rounded-lg text-sm px-3 py-2.5">
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">To Account</label>
                <select name="to_account_id" required class="w-full border-gray-200 rounded-lg text-sm px-3 py-2.5">
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Related Counter (optional)</label>
                <select name="counter_id" class="w-full border-gray-200 rounded-lg text-sm px-3 py-2.5">
                    <option value="">— None —</option>
                    @foreach($counters as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Amount (৳)</label>
                <input type="number" step="0.01" min="0.01" name="amount" required class="w-full border-gray-200 rounded-lg text-sm px-3 py-2.5">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Description</label>
                <input type="text" name="description" required class="w-full border-gray-200 rounded-lg text-sm px-3 py-2.5" placeholder="e.g. Counter 1 to Counter 2 cash shift">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white text-sm font-bold py-3 rounded-xl hover:bg-indigo-700">Transfer Funds</button>
        </form>
    </div>
</x-app-layout>
