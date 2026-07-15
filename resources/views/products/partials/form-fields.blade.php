{{-- Shared product form fields — used by create & edit --}}
@php
    $product = $product ?? null;
    $isEdit = $product !== null;
@endphp

<div class="space-y-5"
     x-data="{
        name: @js(old('name', $product?->name ?? '')),
        color: @js(old('color', $product?->color ?? '')),
        colorHex: @js(old('color_hex', $product?->color_hex ?: '#2563eb')),
        storage: @js(old('storage', $product?->storage ?? '')),
        variantGroup: @js(old('variant_group', $product?->variant_group ?? '')),
        selling: @js(old('selling_price', $product?->selling_price ?? '')),
        original: @js(old('original_price', $product?->original_price ?? '')),
        autoGroup: true,
        slugify(s) {
            return String(s || '').toLowerCase().trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 80);
        },
        syncGroup() {
            if (this.autoGroup && !@js($isEdit && filled($product?->variant_group))) {
                this.variantGroup = this.slugify(this.name);
            }
        },
        pickSwatch(hex, label) {
            this.colorHex = hex;
            if (!this.color) this.color = label;
        }
     }"
     x-init="syncGroup()">

    {{-- 1. Gallery --}}
    <section class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
            <h3 class="text-sm font-semibold text-slate-800">1. Product gallery</h3>
            <p class="text-xs text-slate-500 mt-0.5">Up to 3 photos — shown as thumbnails on the store product page.</p>
        </div>
        <div class="p-4">
            @include('products.partials.image-uploads', ['product' => $product ?? null])
        </div>
    </section>

    {{-- 2. Basic info --}}
    <section class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
            <h3 class="text-sm font-semibold text-slate-800">2. Basic information</h3>
            <p class="text-xs text-slate-500 mt-0.5">Title, brand, and category customers see on the store.</p>
        </div>
        <div class="p-4 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Product name <span class="text-red-500">*</span></label>
                <input type="text" name="name" x-model="name" @input="syncGroup()" value="{{ old('name', $product?->name ?? '') }}" required autofocus
                       class="block w-full rounded-lg border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5"
                       placeholder="e.g. iPhone 15 Pro Max">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Category</label>
                    <select name="category_id" class="block w-full rounded-lg border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product?->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Brand</label>
                    <select name="brand_id" class="block w-full rounded-lg border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5">
                        <option value="">No brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product?->brand_id ?? '') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Barcode <span class="text-red-500">*</span></label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product?->barcode ?? '') }}" required
                           class="block w-full rounded-lg border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 font-mono"
                           placeholder="Scan or type…">
                    @error('barcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">SKU (optional)</label>
                <input type="text" name="sku" value="{{ old('sku', $product?->sku ?? '') }}"
                       class="block w-full md:max-w-xs rounded-lg border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500 text-sm py-2.5 font-mono"
                       placeholder="e.g. IPH15-256-NAT">
            </div>
        </div>
    </section>

    {{-- 3. Pricing --}}
    <section class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
            <h3 class="text-sm font-semibold text-slate-800">3. Pricing</h3>
            <p class="text-xs text-slate-500 mt-0.5">Selling price shows on the store; original price shows as strike-through when higher.</p>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Cost price (Tk) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $product?->cost_price ?? '') }}" required
                       class="block w-full rounded-lg border-slate-200 text-sm py-2.5">
                @error('cost_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Selling price (Tk) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="selling_price" x-model="selling" value="{{ old('selling_price', $product?->selling_price ?? '') }}" required
                       class="block w-full rounded-lg border-slate-200 text-sm py-2.5 font-medium text-blue-600">
                @error('selling_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Original / compare-at (Tk)</label>
                <input type="number" step="0.01" name="original_price" x-model="original" value="{{ old('original_price', $product?->original_price ?? '') }}"
                       class="block w-full rounded-lg border-slate-200 text-sm py-2.5"
                       placeholder="Optional — for % OFF badge">
            </div>
        </div>
    </section>

    {{-- 4. Variants — matches storefront color/storage UI --}}
    <section class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
            <h3 class="text-sm font-semibold text-slate-800">4. Color & storage (store variants)</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Same <strong>variant group</strong> links products (e.g. Red + Blue S22). Customers pick color circles and storage buttons on the store page.
            </p>
        </div>
        <div class="p-4 space-y-4">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <label class="text-xs font-semibold text-slate-600">Variant group key</label>
                    <label class="text-[11px] text-slate-500 inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" x-model="autoGroup" @change="syncGroup()" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Auto from product name
                    </label>
                </div>
                <input type="text" name="variant_group" x-model="variantGroup" @input="autoGroup = false"
                       class="block w-full rounded-lg border-slate-200 text-sm py-2.5 font-mono"
                       placeholder="e.g. samsung-s22">
                <p class="text-[11px] text-slate-400 mt-1">Create another product with the same key + different color to show swatches together.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Color name</label>
                    <input type="text" name="color" x-model="color"
                           class="block w-full rounded-lg border-slate-200 text-sm py-2.5"
                           placeholder="e.g. Natural Titanium, Red">
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        @foreach([
                            ['#1e293b', 'Black'],
                            ['#f8fafc', 'White'],
                            ['#dc2626', 'Red'],
                            ['#2563eb', 'Blue'],
                            ['#d4cfc8', 'Natural Titanium'],
                            ['#3a3a3a', 'Black Titanium'],
                            ['#5b7a9d', 'Blue Titanium'],
                            ['#16a34a', 'Green'],
                            ['#ca8a04', 'Gold'],
                        ] as [$hex, $label])
                            <button type="button" @click="pickSwatch('{{ $hex }}', '{{ $label }}')"
                                    title="{{ $label }}"
                                    class="w-7 h-7 rounded-full border-2 border-white shadow ring-1 ring-slate-200 hover:ring-blue-400 transition"
                                    style="background: {{ $hex }}"></button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Swatch color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" x-model="colorHex"
                               class="h-10 w-14 rounded-lg border border-slate-200 cursor-pointer shrink-0">
                        <input type="text" name="color_hex" x-model="colorHex"
                               class="flex-1 rounded-lg border-slate-200 text-sm py-2.5 font-mono"
                               placeholder="#2563eb">
                        <div class="w-10 h-10 rounded-full border-2 border-blue-600 ring-2 ring-blue-100 shrink-0"
                             :style="'background:' + colorHex" title="Preview"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">This circle appears on the product page.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Storage / size</label>
                <input type="text" name="storage" x-model="storage" list="storage-presets"
                       class="block w-full md:max-w-xs rounded-lg border-slate-200 text-sm py-2.5"
                       placeholder="e.g. 256GB">
                <datalist id="storage-presets">
                    <option value="64GB"><option value="128GB"><option value="256GB"><option value="512GB"><option value="1TB">
                </datalist>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach(['128GB', '256GB', '512GB', '1TB'] as $s)
                        <button type="button" @click="storage = '{{ $s }}'"
                                class="px-2.5 py-1 rounded-md border text-xs font-medium transition"
                                :class="storage === '{{ $s }}' ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                            {{ $s }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Live storefront-style preview --}}
            <div class="rounded-lg border border-dashed border-blue-200 bg-blue-50/40 p-3">
                <p class="text-[11px] font-semibold text-blue-700 uppercase tracking-wide mb-2">Store preview</p>
                <p class="text-sm font-semibold text-slate-900" x-text="name || 'Product name'"></p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-sm font-semibold text-blue-600" x-text="selling ? ('Tk ' + Number(selling).toLocaleString()) : 'Tk —'"></span>
                    <span class="text-xs text-slate-400 line-through" x-show="original && Number(original) > Number(selling)" x-text="'Tk ' + Number(original).toLocaleString()"></span>
                </div>
                <p class="text-xs text-slate-600 mt-2" x-show="storage">
                    Storage: <span class="font-medium" x-text="storage"></span>
                </p>
                <div class="flex items-center gap-2 mt-2" x-show="color || colorHex">
                    <span class="text-xs text-slate-600">Color: <span class="font-medium" x-text="color || '—'"></span></span>
                    <span class="w-5 h-5 rounded-full border-2 border-blue-600 ring-1 ring-blue-100 inline-block" :style="'background:' + colorHex"></span>
                </div>
                <p class="text-[11px] text-slate-400 mt-2" x-show="variantGroup">
                    Group: <code class="bg-white px-1 rounded border border-slate-100" x-text="variantGroup"></code>
                </p>
            </div>
        </div>
    </section>

    {{-- 5. Store description --}}
    <section class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/80">
            <h3 class="text-sm font-semibold text-slate-800">5. Store description & visibility</h3>
            <p class="text-xs text-slate-500 mt-0.5">Shown under Description on the product page.</p>
        </div>
        <div class="p-4 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Short description</label>
                <textarea name="short_description" rows="4"
                          class="block w-full rounded-lg border-slate-200 text-sm"
                          placeholder="About this item — features, condition, what’s included…">{{ old('short_description', $product?->short_description ?? '') }}</textarea>
            </div>

            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                           {{ old('is_published', $isEdit ? ($product?->is_published ?? true) : true) ? 'checked' : '' }}>
                    Publish on website
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_new_arrival" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                           {{ old('is_new_arrival', $product?->is_new_arrival ?? false) ? 'checked' : '' }}>
                    New badge
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                           {{ old('is_featured', $product?->is_featured ?? false) ? 'checked' : '' }}>
                    Featured
                </label>
            </div>

            @if(!$isEdit)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Opening quantity</label>
                        <input type="number" name="stock_quantity" min="0" step="1"
                               value="{{ old('stock_quantity', 0) }}"
                               class="block w-full rounded-lg border-slate-200 text-sm py-2.5"
                               placeholder="e.g. 10">
                        <p class="text-[11px] text-slate-400 mt-1">How many units you have now. Enter 0 if you’ll stock later.</p>
                        @error('stock_quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Low stock alert</label>
                        <input type="number" name="alert_quantity" value="{{ old('alert_quantity', 5) }}" required min="0"
                               class="block w-full rounded-lg border-slate-200 text-sm py-2.5">
                    </div>
                </div>
                <div class="rounded-lg border border-blue-100 bg-blue-50/50 px-3 py-2.5 text-xs text-slate-600">
                    Quantity entered here becomes <strong>opening stock</strong> (same ledger entry as Opening Inventory).
                    Leave <strong>0</strong> if the product isn’t in hand yet — you can still set it later in Opening Inventory.
                    For already-stocked products, use <a href="{{ route('supply.adjustments.index') }}" class="text-blue-600 font-medium underline">Stock Adjustment</a> to change qty.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Current stock</label>
                        <div class="block w-full rounded-lg border border-slate-200 bg-slate-50 text-sm py-2.5 px-3 font-medium text-slate-900">
                            {{ $product?->stock_quantity ?? 0 }} units
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Change stock via
                            @if(($product?->stock_quantity ?? 0) === 0)
                                <a href="{{ route('supply.opening-inventory.index') }}" class="text-blue-600 underline">Opening Inventory</a>
                                or
                            @endif
                            <a href="{{ route('supply.adjustments.index') }}" class="text-blue-600 underline">Stock Adjustment</a>.
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Low stock alert</label>
                        <input type="number" name="alert_quantity" value="{{ old('alert_quantity', $product?->alert_quantity ?? 5) }}" required min="0"
                               class="block w-full rounded-lg border-slate-200 text-sm py-2.5">
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
