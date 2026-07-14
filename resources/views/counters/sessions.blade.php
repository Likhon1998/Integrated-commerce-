<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-6 mt-4 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <h2 class="text-3xl font-black text-gray-950 tracking-tight">Cash Sessions</h2>
                <p class="mt-1 text-sm text-gray-500">Open with starting cash · sell on POS · close with counted cash and sales total.</p>
            </div>
            <a href="{{ route('counters.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">Manage terminals</a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ session('error') }}</div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-1 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-1">Open counter</h3>
                <p class="text-xs text-gray-500 mb-4">Enter the cash in the drawer before the first sale.</p>
                <form method="POST" action="{{ route('counters.sessions.open') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Counter</label>
                        <select name="counter_id" class="w-full text-sm rounded-lg border-gray-200 py-1.5" required>
                            <option value="">Select…</option>
                            @foreach($counters as $counter)
                                <option value="{{ $counter->id }}" @disabled(isset($openSessions[$counter->id]))>
                                    {{ $counter->name }}{{ isset($openSessions[$counter->id]) ? ' (already open)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Starting cash (৳)</label>
                        <input type="number" step="0.01" min="0" name="opening_cash" value="0" class="w-full text-sm rounded-lg border-gray-200 py-1.5" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">Notes</label>
                        <input type="text" name="notes" placeholder="Optional" class="w-full text-sm rounded-lg border-gray-200 py-1.5 placeholder:text-gray-400">
                    </div>
                    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 rounded-lg">Open session</button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-sm font-bold text-gray-900">Currently open</h3>
                @forelse($openSessions as $session)
                    @php
                        $stats = $live[$session->counter_id]['stats'] ?? ['order_count'=>0,'total_sales'=>0,'cash_sales'=>0];
                        $expected = $live[$session->counter_id]['expected'] ?? $session->opening_cash;
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <p class="font-bold text-gray-900">{{ $session->counter->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Opened {{ $session->opened_at->format('M d, h:i A') }} by {{ $session->opener->name ?? '—' }}
                                · Start ৳{{ number_format($session->opening_cash, 2) }}
                            </p>
                            <div class="mt-2 flex flex-wrap gap-3 text-xs">
                                <span class="font-semibold text-gray-700">Sales: ৳{{ number_format($stats['total_sales'], 2) }}</span>
                                <span class="text-gray-500">{{ $stats['order_count'] }} orders</span>
                                <span class="text-gray-500">Cash sales: ৳{{ number_format($stats['cash_sales'], 2) }}</span>
                                <span class="text-emerald-700 font-semibold">Expected drawer: ৳{{ number_format($expected, 2) }}</span>
                            </div>
                        </div>
                        <a href="{{ route('counters.sessions.close-form', $session) }}"
                           class="inline-flex justify-center bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold px-4 py-2 rounded-lg">
                            Close & count cash
                        </a>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-200 p-8 text-center text-sm text-gray-400">
                        No open sessions. Open a counter with starting cash to begin.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b bg-slate-50">
                <h3 class="text-sm font-bold text-gray-900">Session history</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wider text-gray-400 border-b">
                        <th class="text-left font-semibold px-4 py-2">Counter</th>
                        <th class="text-left font-semibold px-2 py-2">Opened</th>
                        <th class="text-left font-semibold px-2 py-2">Status</th>
                        <th class="text-right font-semibold px-2 py-2">Sales</th>
                        <th class="text-right font-semibold px-2 py-2">Open → Close</th>
                        <th class="text-right font-semibold px-2 py-2">Variance</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recent as $session)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $session->counter->name ?? '—' }}</td>
                            <td class="px-2 py-3 text-xs text-gray-500">{{ $session->opened_at->format('M d, H:i') }}</td>
                            <td class="px-2 py-3">
                                <span class="text-[10px] font-bold uppercase px-2 py-1 rounded-md {{ $session->status === 'open' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $session->status }}
                                </span>
                            </td>
                            <td class="px-2 py-3 text-right font-semibold">
                                @if($session->status === 'closed')
                                    ৳{{ number_format($session->total_sales, 2) }}
                                    <span class="block text-[10px] text-gray-400 font-medium">{{ $session->order_count }} orders</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-2 py-3 text-right text-xs text-gray-600">
                                ৳{{ number_format($session->opening_cash, 2) }}
                                @if($session->status === 'closed')
                                    → ৳{{ number_format($session->closing_cash, 2) }}
                                @endif
                            </td>
                            <td class="px-2 py-3 text-right font-semibold {{ ($session->variance ?? 0) < 0 ? 'text-red-600' : (($session->variance ?? 0) > 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                                @if($session->status === 'closed')
                                    ৳{{ number_format($session->variance, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('counters.sessions.show', $session) }}" class="text-indigo-600 font-bold text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-gray-400">No sessions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $recent->links() }}</div>
        </div>
    </div>
</x-app-layout>
