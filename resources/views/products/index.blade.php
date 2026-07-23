<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-10 px-4 sm:px-6 lg:px-8">

        <div class="mb-5 mt-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Product List</h2>
                <p class="mt-0.5 text-sm text-slate-500">Catalog for POS and the online store.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('supply.opening-inventory.index') }}"
                   class="inline-flex items-center gap-1.5 bg-white text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                    Opening Stock
                </a>
                <a href="{{ route('supply.adjustments.index') }}"
                   class="inline-flex items-center gap-1.5 bg-white text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                    Adjust Stock
                </a>
                <a href="{{ route('products.barcodes') }}"
                   class="inline-flex items-center gap-1.5 bg-white text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                    Print Barcodes
                </a>
                <a href="{{ route('products.import') }}"
                   class="inline-flex items-center gap-1.5 bg-white text-slate-600 border border-slate-200 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                    Import CSV
                </a>
                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3.5 py-1.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Product
                </a>
            </div>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-init="setTimeout(() => show = false, 5000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="bg-emerald-50 border border-emerald-100 px-4 py-3 mb-4 rounded-xl flex items-center justify-between">
                <span class="text-sm font-medium text-emerald-800">{{ session('success') }}</span>
                <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <p class="font-bold mb-1">Some CSV rows were skipped:</p>
                <ul class="list-disc pl-5 space-y-0.5 text-xs max-h-40 overflow-y-auto">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500">
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide">Product</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide">Added</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide">Sell</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide">Cost</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide">Stock</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide">Value</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-center">Status</th>
                            <th class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden">
                                            @if($product->image)
                                                <img src="{{ public_storage_url($product->image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                            @else
                                                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-slate-900 truncate max-w-[240px]" title="{{ $product->name }}">{{ $product->name }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                                                <span>{{ $product->category->name ?? 'Uncategorized' }}</span>
                                                @if($product->brand_name || $product->brand)
                                                    <span class="text-slate-300">·</span>
                                                    <span>{{ $product->brand_name ?? $product->brand?->name }}</span>
                                                @endif
                                                <span class="text-slate-300">·</span>
                                                <span class="font-mono text-[11px] text-slate-400">{{ $product->barcode }}</span>
                                                @if($product->is_new_arrival)
                                                    <span class="inline-flex px-1.5 py-0 text-[10px] font-bold uppercase tracking-wide rounded bg-emerald-50 text-emerald-700 border border-emerald-100">New</span>
                                                @endif
                                                @if($product->is_published === false)
                                                    <span class="inline-flex px-1.5 py-0 text-[10px] font-bold uppercase tracking-wide rounded bg-slate-100 text-slate-500 border border-slate-200">Hidden</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-700">{{ $product->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $product->created_at->format('h:i A') }}</div>
                                </td>

                                <td class="px-4 py-3 text-sm font-medium text-slate-900">Tk {{ number_format($product->selling_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500">Tk {{ number_format($product->cost_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $product->stock_quantity }}</td>

                                <td class="px-4 py-3 text-sm text-slate-700">
                                    Tk {{ number_format($product->cost_price * $product->stock_quantity, 2) }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($product->stock_quantity <= ($product->alert_quantity ?? 5))
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md bg-red-50 text-red-600 border border-red-100">Low</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100">OK</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <a href="{{ route('products.barcodes.print', ['product_ids' => $product->id]) }}"
                                           class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition" title="Print barcode">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}"
                                           class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="m-0 inline" onsubmit="return confirm('Delete this product permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-14 text-center">
                                    <p class="text-sm font-medium text-slate-800">No products yet</p>
                                    <p class="text-sm text-slate-500 mt-1 mb-3">Add a product to start selling in POS and online.</p>
                                    <a href="{{ route('products.create') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Add your first product →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($products->count())
                    <tfoot class="bg-slate-50 border-t border-slate-200">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right text-xs font-medium text-slate-500">Page inventory value</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                Tk {{ number_format($products->sum(fn($p) => $p->cost_price * $p->stock_quantity), 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>

    </div>
</x-app-layout>
