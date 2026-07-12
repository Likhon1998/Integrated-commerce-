<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Ledger & Adjustments') }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ show: true }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-green-500 text-white p-3 rounded-lg shadow-sm font-bold flex justify-between items-center text-sm mb-4">
                    {{ session('success') }}
                    <button @click="show = false" class="text-white hover:text-green-200 text-xl leading-none">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-red-500 text-white p-3 rounded-lg shadow-sm font-bold flex justify-between items-center text-sm mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ session('error') }}
                    </div>
                    <button @click="show = false" class="text-white hover:text-red-200 text-xl leading-none">&times;</button>
                </div>
            @endif

            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    New Stock Adjustment
                </h3>
                
                <form action="{{ route('stock.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    @csrf
                    <div class="md:col-span-3">
                        <x-input-label for="product_id" :value="__('Select Product')" class="text-xs" />
                        <select name="product_id" id="product_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 text-sm py-2">
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ Str::limit($product->name, 20) }} (Qty: {{ $product->stock_quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="type" :value="__('Type')" class="text-xs" />
                        <select name="type" id="type" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 text-sm py-2">
                            <option value="in">IN (+)</option>
                            <option value="out">OUT (-)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="quantity" :value="__('Qty')" class="text-xs" />
                        <x-text-input id="quantity" name="quantity" type="number" min="1" required class="mt-1 block w-full text-sm py-2" placeholder="e.g. 5" />
                    </div>
                    <div class="md:col-span-3">
                        <x-input-label for="reference" :value="__('Reason / Ref')" class="text-xs" />
                        <x-text-input id="reference" name="reference" type="text" required class="mt-1 block w-full text-sm py-2" placeholder="e.g. Damaged, Restock" />
                    </div>
                    <div class="md:col-span-2">
                        <x-primary-button class="w-full justify-center py-2.5 text-xs font-black">Update</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-3 border-b border-gray-100 bg-slate-50 flex flex-col md:flex-row justify-between items-center gap-3">
                    <h3 class="font-bold text-gray-700 text-sm">Movement History</h3>
                    <form method="GET" action="{{ route('stock.index') }}" class="flex w-full md:w-auto gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Product or Invoice..." class="w-full md:w-56 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 text-sm py-1.5">
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-md text-xs font-bold hover:bg-slate-900 transition">Search</button>
                        @if(request('search'))
                            <a href="{{ route('stock.index') }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-md text-xs font-bold hover:bg-gray-300 transition flex items-center">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Change</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-gray-500">{{ $movement->created_at->format('d M y, h:i A') }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-sm font-bold text-gray-900">{{ Str::limit($movement->product->name, 25) }}</td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs">
                                    @if(in_array($movement->type, ['in', 'addition']))
                                        <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-bold">IN (+)</span>
                                    @elseif($movement->type === 'sale')
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold">SALE (-)</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded font-bold">OUT (-)</span>
                                    @endif
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap text-sm font-black {{ in_array($movement->type, ['in', 'addition']) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ in_array($movement->type, ['in', 'addition']) ? '+' : '-' }}{{ $movement->quantity }}
                                </td>
                                
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-gray-500 font-mono">
                                    {{ $movement->previous_stock }} &rarr; <span class="font-bold text-gray-900">{{ $movement->current_stock }}</span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-gray-600">{{ Str::limit($movement->reference, 30) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No stock movements found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t border-gray-100 bg-white">
                    {{ $movements->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>