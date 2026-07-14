<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Product') }}
            </h2>
            <a href="{{ $returnTo['url'] }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-indigo-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                {{ $returnTo['label'] }}
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="bg-slate-50 border-b border-gray-100 p-6 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-100 p-2.5 rounded-lg text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-800">Product Specification</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Pricing, stock, photos, and store details customers see online.</p>
                    </div>
                </div>

                <div class="text-right hidden sm:block">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">System Entry Date</p>
                    <span class="bg-white border border-gray-200 text-gray-700 px-3 py-1 rounded-md text-xs font-bold shadow-sm inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ now()->format('d M, Y') }}
                    </span>
                </div>
            </div>

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($returnTo['key'])
                    <input type="hidden" name="return_to" value="{{ $returnTo['key'] }}">
                @endif
                <div class="p-6 space-y-6">
                    
                    @include('products.partials.image-uploads')

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Product Name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   class="pl-10 block w-full rounded-lg border-gray-300 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 transition"
                                   placeholder="e.g. iPhone 15 Pro Max">
                        </div>
                        @error('name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Category</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                </div>
                                <select name="category_id" class="pl-10 block w-full rounded-lg border-gray-300 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 transition">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('category_id') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Brand</label>
                            <select name="brand_id" class="block w-full rounded-lg border-gray-300 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 transition">
                                <option value="">No Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @error('brand_id') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Barcode (Scan Item) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                </div>
                                <input type="text" name="barcode" value="{{ old('barcode') }}" required
                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 transition font-mono"
                                       placeholder="Scan or type barcode...">
                            </div>
                            @error('barcode') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Cost Price (Tk)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-bold text-sm">Tk</span>
                                </div>
                                <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price') }}" required
                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 transition">
                            </div>
                            @error('cost_price') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Selling Price (Tk)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-indigo-500 font-bold text-sm">Tk</span>
                                </div>
                                <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required
                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 transition font-bold text-indigo-600">
                            </div>
                            @error('selling_price') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @include('products.partials.storefront-fields')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 rounded-xl border border-indigo-100 bg-indigo-50/60 p-4">
                            <p class="text-sm font-bold text-indigo-900">Stock quantity</p>
                            <p class="mt-1 text-sm text-indigo-800/80">
                                New products start with <strong>0 stock</strong>. After saving, set quantities in
                                <a href="{{ route('supply.opening-inventory.index') }}" class="font-bold underline hover:text-indigo-600">Opening Inventory</a>
                                so the item can appear for sale online.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Low Stock Alert Level</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <input type="number" name="alert_quantity" value="{{ old('alert_quantity', 5) }}" required
                                       class="pl-10 block w-full rounded-lg border-gray-300 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 transition">
                            </div>
                            @error('alert_quantity') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                </div>

                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <a href="{{ $returnTo['url'] }}" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Product to Inventory
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>