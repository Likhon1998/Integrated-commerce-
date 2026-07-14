<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Product') }}</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition">Back to Product List</a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6">
                    @include('products.partials.image-uploads', ['product' => $product])

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Product Name *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="block w-full rounded-lg border-gray-300 bg-slate-50 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Category</label>
                            <select name="category_id" class="block w-full rounded-lg border-gray-300 bg-slate-50 sm:text-sm py-2.5">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Brand</label>
                            <select name="brand_id" class="block w-full rounded-lg border-gray-300 bg-slate-50 sm:text-sm py-2.5">
                                <option value="">No Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Barcode *</label>
                            <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" required class="block w-full rounded-lg border-gray-300 bg-slate-50 font-mono sm:text-sm py-2.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Cost Price (Tk)</label>
                            <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" required class="block w-full rounded-lg border-gray-300 bg-slate-50 sm:text-sm py-2.5">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Selling Price (Tk)</label>
                            <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required class="block w-full rounded-lg border-gray-300 bg-slate-50 sm:text-sm py-2.5">
                        </div>
                    </div>

                    @include('products.partials.storefront-fields', ['product' => $product])

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Current Stock</label>
                            <div class="block w-full rounded-lg border border-gray-200 bg-gray-50 sm:text-sm py-2.5 px-3 font-bold text-gray-900">
                                {{ $product->stock_quantity }} units
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Change stock in
                                <a href="{{ route('supply.opening-inventory.index') }}" class="font-bold text-indigo-600 hover:underline">Opening Inventory</a>
                                or
                                <a href="{{ route('supply.adjustments.index') }}" class="font-bold text-indigo-600 hover:underline">Stock Adjustment</a>.
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Low Stock Alert</label>
                            <input type="number" name="alert_quantity" value="{{ old('alert_quantity', $product->alert_quantity) }}" required class="block w-full rounded-lg border-gray-300 bg-slate-50 sm:text-sm py-2.5">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 border-t flex justify-end gap-3">
                    <a href="{{ route('products.index') }}" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border rounded-lg">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
