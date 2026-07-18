<x-app-layout>
    <div class="py-8"
         x-data="onlineOrdersHub(@js($ordersPayload ?? []), @js($statusFilter ?? 'all'), @js($search ?? ''), @js($filterDate ?? ''))">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m-9 9a9 9 0 019-9"/></svg>
                        Online Orders Hub
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Manage web orders, track courier receivables, and process refunds.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 bg-white p-2 rounded-xl border border-gray-200 shadow-sm w-full xl:w-auto">
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text"
                               x-model="search"
                               x-ref="search"
                               placeholder="Search Phone or Invoice..."
                               autocomplete="off"
                               class="pl-9 border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5 w-full">
                    </div>
                    <div class="w-full sm:w-px h-px sm:h-6 bg-gray-200"></div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <label for="date" class="text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Date:</label>
                        <input type="date" id="date" x-model="date" @change="applyDate()"
                               class="border-gray-300 rounded-lg shadow-sm text-sm font-bold text-indigo-700 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 cursor-pointer w-full sm:w-auto">
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto" x-show="search || date || status !== 'all'" x-cloak>
                        <button type="button" @click="clearAll()"
                                class="flex-1 sm:flex-none px-4 py-1.5 bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600 text-xs font-bold rounded-lg transition-colors text-center">
                            Clear All
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                <template x-for="tab in statusTabs" :key="tab.key">
                    <button type="button"
                            @click="status = tab.key"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold border"
                            :class="status === tab.key ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                            x-text="tab.label"></button>
                </template>
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
                            <template x-for="order in filteredOrders" :key="order.id">
                                <tr class="bg-white border-b hover:bg-gray-50 transition-colors" :class="order.is_voided ? 'bg-red-50/10' : ''">
                                    <td class="px-6 py-4">
                                        <a :href="order.show_url" class="font-bold text-indigo-600 hover:underline mb-1 block" x-text="order.invoice"></a>
                                        <div class="text-xs text-gray-500 mb-2" x-text="order.created_at"></div>
                                        <div class="text-xs text-gray-600">
                                            <template x-for="(item, idx) in order.items" :key="order.id + '-' + idx">
                                                <div class="truncate max-w-[200px]" x-text="'▪ ' + item.qty + 'x ' + item.name"></div>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900" x-text="order.customer_name"></div>
                                        <div class="text-gray-500" x-text="order.customer_phone"></div>
                                        <div class="text-xs text-gray-400 mt-1 max-w-[200px] truncate" :title="order.customer_address" x-text="order.customer_address"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-black text-gray-900 text-lg">
                                            <span :class="order.is_voided ? 'line-through text-red-500 opacity-60' : ''" x-text="'৳' + order.product_revenue"></span>
                                        </div>
                                        <template x-if="order.delivery_charge > 0">
                                            <div class="text-[10px] text-gray-400 font-bold mb-1">
                                                <span class="text-indigo-500" x-text="'+ ৳' + order.delivery_charge_fmt + ' Courier'"></span> (Excluded)
                                            </div>
                                        </template>
                                        <template x-if="order.delivery_charge <= 0">
                                            <div class="text-[10px] text-gray-500 font-bold mb-1">Free Delivery</div>
                                        </template>
                                        <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider border border-gray-200" x-text="order.payment_method"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold px-3 py-1 rounded-full border"
                                              :class="statusBadgeClass(order.status)"
                                              x-text="statusLabel(order.status)"></span>
                                        <template x-if="order.status === 'shipped' && order.shipping_courier">
                                            <div>
                                                <p class="text-[10px] text-purple-700 font-bold mt-1" x-text="order.shipping_courier"></p>
                                                <p class="text-[10px] text-gray-500 font-mono" x-show="order.shipping_tracking_no" x-text="order.shipping_tracking_no"></p>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col gap-2 justify-end w-full min-w-[130px]">
                                            <a :href="order.show_url" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                                                Manage & track
                                            </a>
                                            <button type="button"
                                                    @click="window.open(order.receipt_url, 'ReceiptWindow', 'width=400,height=620')"
                                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                                Print receipt
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredOrders.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <p class="text-gray-500 font-medium">No online orders match your search.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-3 border-t border-gray-100 text-[11px] text-gray-400" x-show="allOrders.length">
                    Showing <span class="font-semibold text-gray-600" x-text="filteredOrders.length"></span>
                    of <span class="font-semibold text-gray-600" x-text="allOrders.length"></span> loaded orders
                    <span x-show="search"> · filtered instantly</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function onlineOrdersHub(orders, initialStatus, initialSearch, initialDate) {
            return {
                allOrders: orders || [],
                search: initialSearch || '',
                status: initialStatus && initialStatus !== '' ? initialStatus : 'all',
                date: initialDate || '',
                statusTabs: [
                    { key: 'all', label: 'All' },
                    { key: 'pending', label: 'Pending' },
                    { key: 'processing', label: 'Packing' },
                    { key: 'shipped', label: 'Shipped' },
                    { key: 'completed', label: 'Delivered' },
                ],
                get filteredOrders() {
                    const q = (this.search || '').trim().toLowerCase();
                    return this.allOrders.filter((order) => {
                        if (this.status !== 'all' && order.status !== this.status) return false;
                        if (!q) return true;
                        return (order.search_blob || '').includes(q);
                    });
                },
                statusLabel(status) {
                    const map = {
                        pending: 'Pending',
                        processing: 'Processing',
                        shipped: 'Shipped',
                        completed: 'Completed',
                        cancelled: 'Cancelled',
                        returned: 'Returned',
                        refunded: 'Refunded',
                    };
                    return map[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : '');
                },
                statusBadgeClass(status) {
                    const map = {
                        pending: 'bg-amber-100 text-amber-800 border-amber-200',
                        processing: 'bg-blue-100 text-blue-800 border-blue-200',
                        shipped: 'bg-purple-100 text-purple-800 border-purple-200',
                        completed: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        cancelled: 'bg-orange-100 text-orange-800 border-orange-300 line-through decoration-orange-600 decoration-2 font-black',
                        returned: 'bg-red-100 text-red-800 border-red-300 line-through decoration-red-600 decoration-2 font-black',
                        refunded: 'bg-rose-100 text-rose-800 border-rose-300 line-through decoration-rose-600 decoration-2 font-black',
                    };
                    return map[status] || 'bg-gray-100 text-gray-800 border-gray-200';
                },
                applyDate() {
                    const params = new URLSearchParams();
                    if (this.date) params.set('date', this.date);
                    if (this.search) params.set('search', this.search);
                    if (this.status && this.status !== 'all') params.set('status', this.status);
                    window.location.href = @json(route('online-orders.index')) + (params.toString() ? ('?' + params.toString()) : '');
                },
                clearAll() {
                    window.location.href = @json(route('online-orders.index'));
                },
            };
        }
    </script>
</x-app-layout>
