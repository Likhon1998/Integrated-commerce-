<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import Products from CSV') }}</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600">Back to Product List</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg border border-gray-100">
                <form action="{{ route('products.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">CSV File</label>
                        <input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
                        @error('csv_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('products.index') }}" class="px-4 py-2 text-sm font-bold text-gray-600 border rounded-lg">Cancel</a>
                        <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Import Products</button>
                    </div>
                </form>
            </div>

            <div class="bg-slate-50 p-6 rounded-lg border border-slate-200">
                <h3 class="text-sm font-bold text-slate-700 uppercase mb-3">CSV Format</h3>
                <p class="text-sm text-slate-600 mb-3">Required columns: <code class="bg-white px-1 rounded">name, barcode, cost_price, selling_price</code></p>
                <p class="text-sm text-slate-600 mb-3">Optional columns: <code class="bg-white px-1 rounded">category, brand, sku, stock_quantity, alert_quantity</code></p>
                <p class="text-sm text-slate-600 mb-3">If <code class="bg-white px-1 rounded">stock_quantity</code> is provided, opening stock is recorded with a full audit trail (same as Opening Inventory).</p>
                <pre class="text-xs bg-white p-4 rounded border overflow-x-auto">name,barcode,category,brand,cost_price,selling_price,stock_quantity,alert_quantity
Samsung Galaxy Buds,8801234567890,Electronics,Samsung,1200,1999,50,5
Coca Cola 500ml,8809876543210,Beverages,,25,40,200,20</pre>
            </div>
        </div>
    </div>
</x-app-layout>
