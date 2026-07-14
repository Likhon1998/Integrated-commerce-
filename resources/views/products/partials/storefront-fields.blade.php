{{-- Storefront fields shown on the public product page --}}
<div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4 space-y-4">
    <div>
        <h4 class="text-sm font-bold text-slate-800">Online store details</h4>
        <p class="text-xs text-slate-500 mt-0.5">Customers see this on the website product page when they browse and buy.</p>
    </div>

    <div>
        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Short description</label>
        <textarea name="short_description" rows="3"
                  class="block w-full rounded-lg border-gray-300 bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Brief product summary for the store page…">{{ old('short_description', isset($product) ? ($product->short_description ?? '') : '') }}</textarea>
        @error('short_description') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">SKU (optional)</label>
            <input type="text" name="sku" value="{{ old('sku', isset($product) ? ($product->sku ?? '') : '') }}"
                   class="block w-full rounded-lg border-gray-300 bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 font-mono"
                   placeholder="e.g. IPH15-256">
            @error('sku') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Compare-at / original price (Tk)</label>
            <input type="number" step="0.01" name="original_price" value="{{ old('original_price', isset($product) ? ($product->original_price ?? '') : '') }}"
                   class="block w-full rounded-lg border-gray-300 bg-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5"
                   placeholder="Shown crossed out if higher than selling price">
            @error('original_price') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-4 pt-1">
        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
            <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{ old('is_published', isset($product) ? ($product->is_published ?? true) : true) ? 'checked' : '' }}>
            Publish on website
        </label>
        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
            <input type="checkbox" name="is_new_arrival" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{ old('is_new_arrival', isset($product) ? ($product->is_new_arrival ?? false) : false) ? 'checked' : '' }}>
            New arrival
        </label>
        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
            <input type="checkbox" name="is_featured" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                   {{ old('is_featured', isset($product) ? ($product->is_featured ?? false) : false) ? 'checked' : '' }}>
            Featured
        </label>
    </div>
</div>
