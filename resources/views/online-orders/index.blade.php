<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        Online Orders Hub
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Manage web orders, track courier receivables, and process refunds.</p>
                </div>

                <form method="GET" action="{{ route('online-orders.index') }}" class="flex flex-col sm:flex-row items-center gap-3 bg-white p-2 rounded-xl border border-gray-200 shadow-sm w-full xl:w-auto">
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Phone or Invoice..." class="pl-9 border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5 w-full">
                    </div>
                    <div class="w-full sm:w-px h-px sm:h-6 bg-gray-200"></div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="date" class="text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Date:</label>
                        <input type="date" name="date" id="date" value="{{ request('date') }}" class="border-gray-300 rounded-lg shadow-sm text-sm font-bold text-indigo-700 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 cursor-pointer w-full sm:w-auto">
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit" class="flex-1 sm:flex-none px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors">Search</button>
                        @if(request('date') || request('search') || request('status'))
                            <a href="{{ route('online-orders.index') }}" class="flex-1 sm:flex-none px-4 py-1.5 bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600 text-xs font-bold rounded-lg transition-colors text-center inline-block">Clear All</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'processing' => 'Packing', 'shipped' => 'Shipped', 'completed' => 'Delivered'] as $key => $label)
                    <a href="{{ route('online-orders.index', array_merge(request()->only(['search','date']), ['status' => $key])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-bold border {{ ($statusFilter ?? 'all') === $key ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Needs packing</p>
                        <h3 class="text-2xl font-black text-gray-900">{{ $pendingCount ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Packing now</p>
                        <h3 class="text-2xl font-black text-gray-900">{{ $processingCount ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center">📦</div>
                </div>
                <div class="bg-blue-50 p-5 rounded-2xl shadow-sm border border-blue-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-1">Out for delivery</p>
                        <h3 class="text-2xl font-black text-blue-900">{{ $shippedCount ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center">🚚</div>
                </div>
                <div class="bg-sky-50 p-5 rounded-2xl shadow-sm border border-sky-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-sky-600 uppercase tracking-widest mb-1">Money with courier</p>
                        <h3 class="text-2xl font-black text-sky-900">৳{{ number_format($courierReceivables ?? 0, 2) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-sky-100 text-sky-500 rounded-full flex items-center justify-center">💸</div>
                </div>
                <div class="bg-green-50 p-5 rounded-2xl shadow-sm border border-green-200 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-green-600 uppercase tracking-widest mb-1">Settled revenue</p>
                        <h3 class="text-2xl font-black text-green-900">৳{{ number_format($settledRevenue ?? 0, 2) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 text-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Order Details</th>
                                <th scope="col" class="px-6 py-4 font-bold">Customer Info</th>
                                <th scope="col" class="px-6 py-4 font-bold">Product Revenue</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                @php $isVoided = in_array($order->status, ['refunded', 'cancelled', 'returned']); @endphp
                                <tr class="bg-white border-b hover:bg-gray-50 transition-colors {{ $isVoided ? 'bg-red-50/10' : '' }}">
                                    
                                    <td class="px-6 py-4">
                                        <a href="{{ route('online-orders.show', $order) }}" class="font-bold text-indigo-600 hover:underline mb-1 block">{{ $order->invoice_no }}</a>
                                        <div class="text-xs text-gray-500 mb-2">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                        <div class="text-xs text-gray-600">
                                            @foreach($order->items as $item)
                                                <div class="truncate max-w-[200px]">▪ {{ $item->quantity }}x {{ $item->product->name ?? 'Unknown Product' }}</div>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $order->customer->name ?? 'Guest' }}</div>
                                        <div class="text-gray-500">{{ $order->customer->phone ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-400 mt-1 max-w-[200px] truncate" title="{{ $order->customer->address ?? '' }}">
                                            {{ $order->customer->address ?? 'No address provided' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-black text-gray-900 text-lg">
                                            <span class="{{ $isVoided ? 'line-through text-red-500 opacity-60' : '' }}">
                                                ৳{{ number_format($order->total_amount - ($order->delivery_charge ?? 0), 2) }}
                                            </span>
                                        </div>
                                        
                                        @if(($order->delivery_charge ?? 0) > 0)
                                            <div class="text-[10px] text-gray-400 font-bold mb-1">
                                                <span class="text-indigo-500">+ ৳{{ number_format($order->delivery_charge, 2) }} Courier</span> (Excluded)
                                            </div>
                                        @else
                                            <div class="text-[10px] text-gray-500 font-bold mb-1">Free Delivery</div>
                                        @endif

                                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border border-gray-200">
                                            {{ str_replace('_', ' ', $order->payment_method) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        @if($order->status === 'pending')
                                            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full border border-amber-200">Pending</span>
                                        @elseif($order->status === 'processing')
                                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full border border-blue-200">Processing</span>
                                        @elseif($order->status === 'shipped')
                                            <span class="bg-purple-100 text-purple-800 text-xs font-bold px-3 py-1 rounded-full border border-purple-200">Shipped</span>
                                            @if($order->shipping_courier)
                                                <p class="text-[10px] text-purple-700 font-bold mt-1">{{ $order->shipping_courier }}</p>
                                                @if($order->shipping_tracking_no)<p class="text-[10px] text-gray-500 font-mono">{{ $order->shipping_tracking_no }}</p>@endif
                                            @endif
                                        @elseif($order->status === 'completed')
                                            <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200">Completed</span>
                                        @elseif($order->status === 'cancelled')
                                            <span class="bg-orange-100 text-orange-800 text-xs font-black px-3 py-1 rounded-full border border-orange-300 line-through decoration-orange-600 decoration-2">Cancelled</span>
                                        @elseif($order->status === 'returned')
                                            <span class="bg-red-100 text-red-800 text-xs font-black px-3 py-1 rounded-full border border-red-300 line-through decoration-red-600 decoration-2">Returned</span>
                                        @elseif($order->status === 'refunded')
                                            <span class="bg-rose-100 text-rose-800 text-xs font-black px-3 py-1 rounded-full border border-rose-300 line-through decoration-rose-600 decoration-2">Refunded</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 text-xs font-bold px-3 py-1 rounded-full border border-gray-200">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col gap-2 justify-end w-full min-w-[130px]">
                                            <a href="{{ route('online-orders.show', $order) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                                Manage & track
                                            </a>
                                            <button onclick="window.open('{{ route('pos.receipt', $order->id) }}', 'ReceiptWindow', 'width=400,height=620')" 
                                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                                Print receipt
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <p class="text-gray-500 font-medium">No online orders match your search.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>