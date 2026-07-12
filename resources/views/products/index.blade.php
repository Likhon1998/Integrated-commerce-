<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 mt-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-950 tracking-tight">Product List</h2>
                <p class="mt-1 text-sm text-gray-500 font-medium leading-relaxed">Manage your catalog — synced to POS terminal and your online store.</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.barcodes') }}" target="_blank" class="inline-flex items-center gap-2 bg-white text-gray-700 border border-gray-200 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">
                    Print Barcodes
                </a>
                <a href="{{ route('products.import') }}" class="inline-flex items-center gap-2 bg-white text-gray-700 border border-gray-200 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">
                    Import CSV
                </a>
                <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all shadow-md shadow-indigo-600/20 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add New Product
                </a>
            </div>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="bg-emerald-50 border border-emerald-100 p-5 mb-8 rounded-2xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="bg-emerald-100/50 p-2 rounded-full flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="font-bold text-emerald-900">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-50">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-900 text-white">
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em] rounded-tl-[30px]">Product Details</th>
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em]">Date Added</th>
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em]">Selling Price</th>
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em]">Unit Cost</th>
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em]">Stock</th>
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em] text-indigo-300">Total Value</th>
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em] text-center">Status</th>
                            <th class="p-5 text-[11px] font-black uppercase tracking-[0.15em] text-right rounded-tr-[30px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($products as $product)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                
                                <td class="p-5 flex items-center gap-4">
                                    <div class="h-14 w-14 flex-shrink-0 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden shadow-sm">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        @endif
                                    </div>
                                    
                                    <div>
                                        <div class="text-sm font-extrabold text-gray-950">{{ $product->name }}</div>
                                        <div class="text-xs text-gray-500 font-medium mt-0.5 flex items-center gap-1.5">
                                            <span>{{ $product->category->name ?? 'Uncategorized' }}</span>
                                            @if($product->brand_name || $product->brand)
                                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                <span>{{ $product->brand_name ?? $product->brand?->name }}</span>
                                            @endif
                                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                            <span class="font-mono text-[10px] text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100 tracking-widest">{{ $product->barcode }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-5">
                                    <span class="text-sm font-extrabold text-gray-900 block mb-0.5">{{ $product->created_at->format('d M, Y') }}</span>
                                    <span class="text-[11px] text-gray-500 font-semibold">{{ $product->created_at->format('h:i A') }}</span>
                                </td>

                                <td class="p-5 text-sm font-black text-gray-900">৳{{ number_format($product->selling_price, 2) }}</td>
                                <td class="p-5 text-sm font-semibold text-gray-500">৳{{ number_format($product->cost_price, 2) }}</td>
                                
                                <td class="p-5 text-sm font-black text-gray-900">{{ $product->stock_quantity }}</td>
                                
                                <td class="p-5">
                                    <span class="text-sm font-black text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                                        ৳{{ number_format($product->cost_price * $product->stock_quantity, 2) }}
                                    </span>
                                </td>

                                <td class="p-5 text-center">
                                    @if($product->stock_quantity <= ($product->alert_quantity ?? 5))
                                        <span class="px-3 py-1 text-[10px] font-black bg-red-50 text-red-600 border border-red-200 rounded-lg uppercase tracking-widest animate-pulse">Low Stock</span>
                                    @else
                                        <span class="px-3 py-1 text-[10px] font-black bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg uppercase tracking-widest">Stable</span>
                                    @endif
                                </td>

                                <td class="p-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('products.barcodes', ['product_ids' => $product->id]) }}" target="_blank" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Print Barcode">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit Product">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="m-0 inline-block" onsubmit="return confirm('Delete this product permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Product">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-16 text-center">
                                    <div class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    </div>
                                    <p class="text-gray-900 font-bold text-lg">Inventory is Empty</p>
                                    <p class="text-gray-500 font-medium text-sm mt-1 mb-4">You haven't added any products to your store yet.</p>
                                    <a href="{{ route('products.create') }}" class="text-indigo-600 font-bold text-sm hover:text-indigo-800 transition">Add your first product &rarr;</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="5" class="p-6 text-right text-xs font-black text-gray-400 uppercase tracking-widest">Page Total Inventory Value:</td>
                            <td class="p-6 text-xl font-black text-indigo-950 tracking-tight">
                                ৳{{ number_format($products->sum(fn($p) => $p->cost_price * $p->stock_quantity), 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="mt-6">
            {{ $products->links() }}
        </div>
        
    </div>
</x-app-layout>