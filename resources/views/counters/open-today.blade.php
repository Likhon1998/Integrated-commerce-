<x-app-layout>
    <div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">

            <div class="text-center mb-6">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Opening balance</h2>
                <p class="mt-1.5 text-sm text-slate-500">Count the cash in your drawer, then start your day.</p>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Your counter</p>
                        <p class="text-base font-bold text-slate-900">{{ $counter->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Date</p>
                        <p class="text-sm font-semibold text-slate-700">{{ now()->format('M j, Y') }}</p>
                    </div>
                </div>

                @if($staleSession)
                    <div class="p-5 space-y-4">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 font-medium">
                            A session from <strong>{{ $staleSession->opened_at->format('M j, g:i A') }}</strong> is still open.
                            Close it first, then enter today&rsquo;s opening cash.
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                                <p class="text-[11px] font-bold text-slate-400 uppercase">Started with</p>
                                <p class="font-bold text-slate-800">৳{{ number_format($staleSession->opening_cash, 2) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-2.5">
                                <p class="text-[11px] font-bold text-slate-400 uppercase">Expected now</p>
                                <p class="font-bold text-emerald-700">৳{{ number_format($expected, 2) }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('counters.sessions.close', $staleSession) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">Counted cash in drawer (৳) *</label>
                                <input type="number" name="closing_cash" step="0.01" min="0" required value="{{ old('closing_cash', $expected) }}"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3 font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1.5">Notes</label>
                                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Optional"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3">
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-3 transition">
                                Close previous session
                            </button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('counters.sessions.open-today.store') }}" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label for="opening_cash" class="block text-xs font-bold text-slate-500 mb-1.5">Opening cash in drawer (৳) *</label>
                            <input id="opening_cash" type="number" name="opening_cash" step="0.01" min="0" required autofocus
                                   value="{{ old('opening_cash', '0') }}"
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3 px-4 font-black text-slate-900">
                            <p class="mt-1.5 text-xs text-slate-500">Enter the exact cash amount before your first sale today.</p>
                        </div>
                        <div>
                            <label for="notes" class="block text-xs font-bold text-slate-500 mb-1.5">Notes</label>
                            <input id="notes" type="text" name="notes" value="{{ old('notes') }}" placeholder="Optional"
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-3">
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-3 shadow-sm transition">
                            Start day &amp; open POS
                        </button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-sm font-semibold text-slate-400 hover:text-slate-600">Sign out</button>
            </form>
        </div>
    </div>
</x-app-layout>
