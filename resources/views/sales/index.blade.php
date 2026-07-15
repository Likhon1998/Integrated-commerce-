<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales Ledger') }}
            </h2>
            <a href="{{ route('pos.index') }}" target="_blank" rel="noopener" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 px-4 rounded-lg shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Open POS Terminal
            </a>
        </div>
    </x-slot>

    <div class="py-4" x-data="{ show: true }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-green-500 text-white p-3 rounded-lg shadow-sm font-bold flex justify-between items-center text-sm">
                    {{ session('success') }}
                    <button @click="show = false" class="text-white hover:text-green-200 text-xl leading-none">&times;</button>
                </div>
            @endif
            @if (session('error'))
                <div x-show="show" x-init="setTimeout(() => show = false, 8000)" class="bg-red-500 text-white p-3 rounded-lg shadow-sm font-bold flex justify-between items-center text-sm">
                    {{ session('error') }}
                    <button @click="show = false" class="text-white hover:text-red-200 text-xl leading-none">&times;</button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                <div class="bg-orange-50 border border-orange-200 p-4 rounded-xl flex justify-between items-center shadow-sm">
                    <div>
                        <p class="text-[10px] font-black text-orange-600 uppercase tracking-widest">Cancelled Orders</p>
                        <h3 class="text-2xl font-black text-orange-900">{{ $cancelledCount ?? 0 }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 p-4 rounded-xl flex justify-between items-center shadow-sm">
                    <div>
                        <p class="text-[10px] font-black text-red-600 uppercase tracking-widest">Returned Orders</p>
                        <h3 class="text-2xl font-black text-red-900">{{ $returnedCount ?? 0 }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-red-100 text-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                    </div>
                </div>

                <div class="bg-rose-50 border border-rose-200 p-4 rounded-xl flex justify-between items-center shadow-sm">
                    <div>
                        <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Refunded Orders</p>
                        <h3 class="text-2xl font-black text-rose-900">{{ $refundedCount ?? 0 }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-rose-100 text-rose-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 relative">
                
                <div class="p-3 border-b border-gray-100 bg-slate-50 flex flex-col md:flex-row justify-between items-center gap-3">
                    <h3 class="font-bold text-gray-700 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Transaction History
                    </h3>

                    <form method="GET" action="{{ route('sales.index') }}" class="flex w-full md:w-auto gap-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice or Mobile..." class="pl-8 w-full md:w-56 border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 text-sm py-1.5 transition">
                        </div>
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold hover:bg-slate-900 transition shadow-sm">Search</button>
                        @if(request('search'))
                            <a href="{{ route('sales.index') }}" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-200 border border-gray-200 transition flex items-center">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Invoice No</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Customer Details</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Product Revenue</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status/Payment</th>
                                <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Cashier</th>
                                <th class="px-4 py-2.5 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($orders as $order)
                            @php $isVoided = in_array($order->status, ['refunded', 'cancelled', 'returned']); @endphp
                            
                            <tr class="hover:bg-gray-50 transition {{ $isVoided ? 'bg-red-50/30' : '' }}">
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-gray-500 font-medium">
                                    {{ $order->created_at->format('d M y, h:i A') }}
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap text-sm font-black tracking-tight {{ $isVoided ? 'text-red-500 line-through opacity-70' : 'text-indigo-600' }}">
                                    {{ $order->invoice_no }}
                                    @if($order->is_exchange_receipt)
                                        <br><span class="text-[9px] text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded uppercase tracking-widest mt-1 inline-block">Exchange Receipt</span>
                                    @endif
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($order->customer)
                                        <div class="text-xs font-bold text-gray-900">{{ $order->customer->name }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 font-mono">{{ $order->customer->phone }}</div>
                                    @else
                                        <span class="text-xs text-gray-400 italic font-medium">Guest Customer</span>
                                    @endif
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="text-sm font-black tracking-tight {{ $isVoided ? 'text-red-500 line-through opacity-70' : 'text-gray-900' }}">
                                        <span class="text-gray-400 text-xs mr-0.5">৳</span>{{ number_format($order->total_amount - ($order->delivery_charge ?? 0), 2) }}
                                    </div>
                                    @if(($order->delivery_charge ?? 0) > 0)
                                        <div class="text-[9px] text-indigo-500 font-bold mt-0.5">+ ৳{{ number_format($order->delivery_charge, 2) }} Courier</div>
                                    @endif
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($order->status === 'refunded')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 line-through decoration-rose-600 decoration-2">Refunded</span>
                                    @elseif($order->status === 'returned')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-800 border border-red-300 line-through decoration-red-600 decoration-2">Returned</span>
                                    @elseif($order->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-orange-100 text-orange-800 border border-orange-300 line-through decoration-orange-600 decoration-2">Cancelled</span>
                                    @else
                                        @if(strtolower($order->payment_method) === 'cash')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-800 border border-green-200">💵 Cash</span>
                                        @elseif(strtolower($order->payment_method) === 'card')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">💳 Card</span>
                                        @elseif(strtolower($order->payment_method) === 'bkash')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-pink-100 text-pink-800 border border-pink-200">📱 bKash</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-800 border border-gray-200">{{ $order->payment_method }}</span>
                                        @endif
                                    @endif
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-gray-500 font-medium">
                                    {{ $order->user->name ?? 'Unknown' }}
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap text-right flex justify-end gap-2">
                                    <button onclick="window.open('{{ route('pos.receipt', $order->id) }}', 'ReceiptWindow', 'width=400,height=600')" 
                                            class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 px-2.5 py-1 rounded text-xs font-bold transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Receipt
                                    </button>

                                    @if(!$isVoided)
                                        @if($order->is_exchange_receipt)
                                            <span class="inline-flex items-center gap-1 text-gray-400 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded text-xs font-bold cursor-not-allowed" title="Items from an exchange cannot be refunded or exchanged again.">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                Final Sale
                                            </span>
                                        @elseif($order->created_at >= now()->subDays(7))
                                            <button type="button" 
                                                data-order-id="{{ $order->id }}"
                                                data-items='{{ json_encode($order->items->map(function($item) { return ["id" => $item->product_id, "name" => $item->product ? $item->product->name : "Unknown Item", "price" => $item->unit_price, "qty" => $item->quantity]; })) }}'
                                                onclick="openReturnModal(this)" 
                                                class="inline-flex items-center gap-1.5 text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-100 px-2.5 py-1 rounded text-xs font-bold transition shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                                Return / Exchange
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-gray-400 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded text-xs font-bold cursor-not-allowed" title="Exchange period (7 days) has expired">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Expired
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No sales found matching your criteria.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                <div class="p-3 border-t border-gray-100 bg-white">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div id="returnModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]">
            <div class="bg-slate-900 p-6 flex flex-col gap-5 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight">Process Transaction</h3>
                        <p class="text-slate-400 text-sm mt-1">Modifying Order #<span id="modalOrderId" class="font-bold text-indigo-400"></span></p>
                    </div>
                    <button type="button" onclick="closeReturnModal()" class="w-8 h-8 bg-white/10 hover:bg-rose-500 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex gap-2 bg-slate-800 p-1.5 rounded-xl border border-white/5">
                    <button id="tabRefundBtn" onclick="switchTab('refund')" class="flex-1 py-2 text-sm font-black rounded-lg bg-rose-500 text-white shadow-sm transition-all flex justify-center items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        Full Refund
                    </button>
                    <button id="tabExchangeBtn" onclick="switchTab('exchange')" class="flex-1 py-2 text-sm font-black rounded-lg text-slate-400 hover:text-white transition-all flex justify-center items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        Product Exchange
                    </button>
                </div>
            </div>

            <div id="tabRefund" class="p-8 overflow-y-auto bg-slate-50 flex-1">
                <div class="bg-rose-100/50 border border-rose-200 p-5 rounded-2xl flex gap-4 items-start mb-6">
                    <div class="text-rose-600 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-rose-900">Warning: Destructive Action</p>
                        <p class="text-xs text-rose-700 mt-1 font-medium leading-relaxed">This will refund the entire order, cross out the receipt, and return all items to inventory stock. This action cannot be undone.</p>
                    </div>
                </div>

                <form id="refundForm" method="POST" action="">
                    @csrf
                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" onclick="closeReturnModal()" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-rose-600 text-white font-black rounded-xl hover:bg-rose-700 shadow-lg shadow-rose-200 transition-colors flex items-center gap-2">
                            Confirm Full Refund
                        </button>
                    </div>
                </form>
            </div>

            <div id="tabExchange" class="p-8 overflow-y-auto bg-slate-50 flex-1 hidden">
                <div class="mb-6 bg-indigo-100/50 border border-indigo-200 p-4 rounded-2xl flex gap-4 items-start">
                    <div class="text-indigo-500 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-indigo-900">Exchange via POS Terminal</p>
                        <p class="text-xs text-indigo-700 mt-1 font-medium">Select the item the customer is returning. You will be automatically redirected to the POS Terminal with their credit applied.</p>
                    </div>
                </div>

                <form id="exchangeForm" method="POST" action="">
                    @csrf
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative pt-6 mb-6">
                        <span class="absolute -top-3 left-4 bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">Returning Item</span>
                        
                        <label class="block text-xs font-bold text-slate-500 mb-2 mt-2">Select Item to Return</label>
                        <select name="return_product_id" id="returnProductSelect" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-xl focus:ring-rose-500 focus:border-rose-500 p-2.5 mb-4 cursor-pointer">
                            </select>

                        <label class="block text-xs font-bold text-slate-500 mb-2">Quantity Returned</label>
                        <input type="number" name="return_qty" id="returnQtyInput" value="1" min="1" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-black rounded-xl focus:ring-rose-500 focus:border-rose-500 p-2.5">
                        <p class="text-[10px] text-slate-400 mt-1" id="returnQtyHelp">Select an item to see purchased quantity.</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeReturnModal()" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-black rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-colors flex items-center gap-2">
                            Proceed to POS
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('returnModal');
        const modalOrderId = document.getElementById('modalOrderId');
        const refundForm = document.getElementById('refundForm');
        const exchangeForm = document.getElementById('exchangeForm');
        const tabRefundBtn = document.getElementById('tabRefundBtn');
        const tabExchangeBtn = document.getElementById('tabExchangeBtn');
        const tabRefund = document.getElementById('tabRefund');
        const tabExchange = document.getElementById('tabExchange');
        const returnProductSelect = document.getElementById('returnProductSelect');
        const returnQtyInput = document.getElementById('returnQtyInput');
        const returnQtyHelp = document.getElementById('returnQtyHelp');

        function openReturnModal(btn) {
            const orderId = btn.getAttribute('data-order-id');
            const purchasedItems = JSON.parse(btn.getAttribute('data-items'));
            modalOrderId.innerText = orderId;
            refundForm.action = `/sales/${orderId}/refund`;
            exchangeForm.action = `/orders/${orderId}/exchange`;
            returnProductSelect.innerHTML = '<option value="">-- Choose Purchased Item --</option>';
            purchasedItems.forEach(item => {
                returnProductSelect.innerHTML += `<option value="${item.id}" data-maxqty="${item.qty}">📦 ${item.name} (৳${item.price} - Bought: ${item.qty})</option>`;
            });
            returnQtyInput.value = 1;
            returnQtyInput.removeAttribute('max');
            returnQtyHelp.innerText = 'Select an item to see purchased quantity.';
            switchTab('refund');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.children[0].classList.remove('scale-95');
            }, 10);
        }

        returnProductSelect.addEventListener('change', function() {
            if (this.selectedIndex > 0) {
                const maxQty = this.options[this.selectedIndex].getAttribute('data-maxqty');
                returnQtyInput.max = maxQty;
                returnQtyHelp.innerText = `Maximum quantity allowed: ${maxQty}`;
                if (parseInt(returnQtyInput.value) > parseInt(maxQty)) {
                    returnQtyInput.value = maxQty;
                }
            } else {
                returnQtyInput.removeAttribute('max');
                returnQtyHelp.innerText = 'Select an item to see purchased quantity.';
            }
        });

        returnQtyInput.addEventListener('input', function() {
            const max = parseInt(this.max);
            if (max && parseInt(this.value) > max) {
                this.value = max;
            }
        });

        function closeReturnModal() {
            modal.classList.add('opacity-0');
            modal.children[0].classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        function switchTab(tab) {
            if (tab === 'refund') {
                tabRefund.classList.remove('hidden'); tabExchange.classList.add('hidden');
                tabRefundBtn.classList.add('bg-rose-500', 'text-white', 'shadow-sm'); tabRefundBtn.classList.remove('text-slate-400');
                tabExchangeBtn.classList.remove('bg-indigo-500', 'text-white', 'shadow-sm'); tabExchangeBtn.classList.add('text-slate-400');
            } else {
                tabExchange.classList.remove('hidden'); tabRefund.classList.add('hidden');
                tabExchangeBtn.classList.add('bg-indigo-500', 'text-white', 'shadow-sm'); tabExchangeBtn.classList.remove('text-slate-400');
                tabRefundBtn.classList.remove('bg-rose-500', 'text-white', 'shadow-sm'); tabRefundBtn.classList.add('text-slate-400');
            }
        }
    </script>
</x-app-layout>