<x-app-layout>
    <div class="py-10 bg-[#f8fafc] min-h-screen relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-slate-900 rounded-[40px] p-8 lg:p-10 shadow-2xl border border-slate-800 mb-10 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-80 h-80 bg-indigo-600/20 rounded-full blur-[100px] pointer-events-none"></div>

                <div class="relative z-10 mb-8 text-center md:text-left">
                    <h2 class="text-3xl lg:text-4xl font-black text-white tracking-tighter mb-2">Staff Activity & Sales Log</h2>
                    <p class="text-slate-400 font-medium text-sm">Detailed daily record of which employee made sales and from which counter.</p>
                </div>

                <form method="GET" action="{{ route('reports.staff_performance') }}" class="relative z-10 flex flex-col md:flex-row gap-4 bg-white/5 backdrop-blur-xl p-6 rounded-[32px] border border-white/10 shadow-inner">
                    <div class="flex-1">
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2 ml-2">Filter by Employee</label>
                        <select name="staff_id" class="w-full bg-slate-800 border border-slate-700 text-white text-sm font-bold rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 block p-4 shadow-sm cursor-pointer">
                            <option value="">📋 All Staff Members</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" {{ $selectedStaffId == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ ucfirst($staff->role ?? 'Staff') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1">
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2 ml-2">From Date</label>
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-white text-sm font-bold rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 block p-4 shadow-sm">
                    </div>

                    <div class="flex-1">
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2 ml-2">To Date</label>
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-white text-sm font-bold rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 block p-4 shadow-sm">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full md:w-auto px-8 py-4 bg-indigo-500 hover:bg-indigo-400 text-white font-black rounded-2xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Search
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Activity Log</h3>
                        <p class="text-xs font-bold text-slate-500 mt-1">
                            {{ $startDate->format('d M, Y') }} — {{ $endDate->format('d M, Y') }}
                        </p>
                    </div>
                    @if($selectedStaffId)
                        <span class="px-4 py-2 bg-indigo-50 text-indigo-600 text-xs font-black uppercase tracking-widest rounded-xl border border-indigo-100">
                            Filtered By Employee
                        </span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400">
                                <th class="p-6 font-black">Sales Date</th>
                                <th class="p-6 font-black">Employee</th>
                                <th class="p-6 font-black">Counter/Terminal</th>
                                <th class="p-6 font-black text-center">Orders</th>
                                <th class="p-6 font-black text-right">Revenue</th>
                                <th class="p-6 font-black text-right">Receipts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($staffPerformance as $row)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    
                                    <td class="p-6">
                                        <p class="text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($row->sale_date)->format('d M Y') }}</p>
                                        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($row->sale_date)->format('l') }}</p>
                                    </td>

                                    <td class="p-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-black shadow-sm text-xs">
                                                {{ substr($row->user->name ?? '?', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900">{{ $row->user->name ?? 'Deleted Staff' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="p-6">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-[10px] uppercase tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                                            {{ $row->counter->name ?? 'Unknown Counter' }}
                                        </span>
                                    </td>

                                    <td class="p-6 text-center">
                                        <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full bg-slate-100 text-slate-700 font-black text-xs">
                                            {{ $row->total_orders }}
                                        </span>
                                    </td>

                                    <td class="p-6 text-right">
                                        <p class="text-lg font-black text-emerald-600 tracking-tight">৳{{ number_format($row->total_revenue, 2) }}</p>
                                    </td>

                                    <td class="p-6 text-right">
                                        <button type="button" onclick="openDetailsModal('{{ $row->user_id }}', '{{ $row->sale_date }}', '{{ $row->counter_id }}')" class="px-4 py-2 bg-slate-900 text-white hover:bg-indigo-600 rounded-xl text-xs font-black transition-all flex items-center justify-end gap-2 ml-auto shadow-md">
                                            View Details
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-16 text-center">
                                        <p class="text-lg font-black text-slate-800 mb-1">No Activity Found</p>
                                        <p class="text-sm text-slate-500">There are no sales records matching your criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($staffPerformance->hasPages())
                    <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                        {{ $staffPerformance->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <div id="detailsModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6 opacity-0 transition-opacity duration-300">
        
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden transform scale-95 transition-transform duration-300 relative" id="modalContent">
            
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-900 text-white">
                <div>
                    <span class="bg-indigo-500/20 text-indigo-300 text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-[0.2em] mb-3 inline-block">Order Details</span>
                    <h3 class="text-2xl font-black tracking-tight" id="modalStaffName">Loading...</h3>
                    <div class="flex items-center gap-4 mt-2">
                        <p class="text-slate-400 font-medium text-xs flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span id="modalDate">Loading...</span>
                        </p>
                        <p class="text-indigo-300 font-bold text-xs flex items-center gap-1.5 bg-indigo-500/20 px-2 py-0.5 rounded-md">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span id="modalCounter">Loading...</span>
                        </p>
                    </div>
                </div>

                <button type="button" onclick="closeDetailsModal()" class="w-10 h-10 bg-white/10 hover:bg-rose-500 rounded-full flex items-center justify-center text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex gap-4 p-6 bg-slate-50 border-b border-slate-100">
                <div class="flex-1 bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-1">Orders Processed</p>
                    <p class="text-2xl font-black text-slate-900" id="modalTotalOrders">-</p>
                </div>
                <div class="flex-1 bg-white p-4 rounded-2xl shadow-sm border border-slate-100 text-center">
                    <p class="text-[10px] uppercase tracking-widest font-bold text-emerald-500 mb-1">Revenue Generated</p>
                    <p class="text-2xl font-black text-emerald-500 tracking-tight" id="modalTotalRevenue">-</p>
                </div>
            </div>

            <div class="overflow-y-auto flex-1 p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-100">
                            <th class="pb-3 font-black">Time</th>
                            <th class="pb-3 font-black">Order ID</th>
                            <th class="pb-3 font-black">Customer</th>
                            <th class="pb-3 font-black text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody" class="divide-y divide-slate-50">
                        </tbody>
                </table>
            </div>
            
            <div class="p-6 bg-slate-50 border-t border-slate-100 text-center">
                <button type="button" onclick="closeDetailsModal()" class="px-8 py-3 bg-slate-900 text-white font-black rounded-xl hover:bg-slate-800 transition-colors text-sm">Close Window</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('detailsModal');
        const modalContent = document.getElementById('modalContent');
        const tbody = document.getElementById('modalTableBody');

        function openDetailsModal(staffId, date, counterId) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
            }, 10);
            
            tbody.innerHTML = '<tr><td colspan="4" class="py-12 text-center text-slate-500 font-bold animate-pulse">Loading exact receipts...</td></tr>';
            
            // Fetch data (We pass staff_id, date, AND counter_id)
            fetch(`{{ route('reports.staff_daily_details') }}?staff_id=${staffId}&date=${date}&counter_id=${counterId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalStaffName').textContent = data.staff_name;
                    document.getElementById('modalDate').textContent = data.date_formatted;
                    document.getElementById('modalCounter').textContent = 'Counter: ' + data.counter_name;
                    document.getElementById('modalTotalOrders').textContent = data.total_orders;
                    document.getElementById('modalTotalRevenue').textContent = '৳' + data.total_revenue;

                    tbody.innerHTML = '';
                    if (data.orders.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="py-12 text-center text-slate-500 font-bold">No transactions found.</td></tr>';
                    } else {
                        data.orders.forEach(order => {
                            tbody.innerHTML += `
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 font-black text-slate-900 text-sm">${order.time}</td>
                                    <td class="py-4 font-black text-indigo-600 text-sm">#${order.id}</td>
                                    <td class="py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px] uppercase">
                                            ${order.customer}
                                        </span>
                                    </td>
                                    <td class="py-4 text-right text-emerald-600 font-black tracking-tight text-lg">৳${order.amount}</td>
                                </tr>
                            `;
                        });
                    }
                })
                .catch(error => {
                    tbody.innerHTML = '<tr><td colspan="4" class="py-12 text-center text-rose-500 font-bold">Failed to load data. Please refresh.</td></tr>';
                });
        }

        function closeDetailsModal() {
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeDetailsModal();
            }
        }
    </script>
</x-app-layout>