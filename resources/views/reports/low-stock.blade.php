<x-app-layout>
    <div class="max-w-7xl mx-auto pt-8 pb-12 px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 bg-slate-900 rounded-[32px] p-8 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-64 h-64 bg-red-500 opacity-10 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none animate-pulse"></div>

            <div class="flex items-center gap-5 relative z-10">
                <div class="w-16 h-16 bg-red-500/20 border border-red-500/30 text-red-400 rounded-2xl flex items-center justify-center shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <span class="bg-red-500 text-white text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-widest mb-2 inline-block shadow-sm">Critical Alert</span>
                    <h2 class="text-3xl font-black text-white tracking-tight">Low Stock Alerts</h2>
                    <p class="text-sm text-slate-300 font-medium mt-1">Items that have fallen below their custom alert quantity.</p>
                </div>
            </div>
            
            <div class="relative z-10 text-right bg-white/10 p-4 rounded-2xl border border-white/20 backdrop-blur-sm">
                <p class="text-slate-300 text-[10px] font-bold uppercase tracking-wider mb-1">Alert System</p>
                <p class="text-white text-lg font-black"><span class="text-emerald-400">Active</span> & Monitoring</p>
            </div>
        </div>

        <div class="bg-white rounded-[32px] shadow-sm border border-gray-100 overflow-hidden ring-1 ring-gray-50">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] pl-8">Product Name</th>
                            <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em]">Category</th>
                            <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em]">SKU / Code</th>
                            <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] text-center">Current Stock</th>
                            <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] text-right pr-8">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($lowStockItems as $item)
                            <tr class="hover:bg-red-50/30 transition-colors group">
                                <td class="p-5 pl-8 font-extrabold text-gray-900">
                                    {{ $item->name }}
                                </td>
                                <td class="p-5 text-sm font-medium text-gray-600">
                                    {{ $item->category->name ?? 'Uncategorized' }}
                                </td>
                                <td class="p-5">
                                    <span class="text-[11px] font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                        {{ $item->sku ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-5 text-center">
                                    <div class="flex flex-col items-center justify-center gap-1">
                                        @if($item->stock_quantity <= 0)
                                            <span class="bg-red-100 text-red-700 font-black text-sm px-4 py-1.5 rounded-lg border border-red-200 shadow-sm animate-pulse">
                                                Out of Stock (0)
                                            </span>
                                        @else
                                            <span class="bg-amber-100 text-amber-700 font-black text-sm px-4 py-1.5 rounded-lg border border-amber-200">
                                                Only {{ $item->stock_quantity }} left
                                            </span>
                                        @endif
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">
                                            Alert at {{ $item->alert_quantity }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-5 text-right pr-8">
                                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg transition-colors border border-indigo-100 shadow-sm">
                                        Update Stock &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-16 text-center">
                                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="text-gray-900 font-bold text-lg mb-1">Stock Levels are Healthy!</p>
                                    <p class="text-gray-500 font-medium text-sm">No items have fallen below their custom alert quantities.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($lowStockItems->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $lowStockItems->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>