{{-- Expects optional $product when editing --}}
@php
    $slots = [
        ['field' => 'image', 'label' => 'Picture 1 (Main)', 'hint' => 'Shown first on the store & product cards'],
        ['field' => 'image_2', 'label' => 'Picture 2', 'hint' => 'Gallery image on product page'],
        ['field' => 'image_3', 'label' => 'Picture 3', 'hint' => 'Gallery image on product page'],
    ];
@endphp
<div class="md:col-span-2">
    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Product pictures (up to 3)</label>
    <p class="text-xs text-gray-500 mb-3">These photos appear on the online store product page. PNG, JPG, or WEBP up to 2MB each.</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4"
         x-data="{
            previews: {
                image: @js(isset($product) && $product->image ? asset('storage/'.$product->image) : null),
                image_2: @js(isset($product) && $product->image_2 ? asset('storage/'.$product->image_2) : null),
                image_3: @js(isset($product) && $product->image_3 ? asset('storage/'.$product->image_3) : null)
            },
            setPreview(key, event) {
                const file = event.target.files[0];
                this.previews[key] = file ? URL.createObjectURL(file) : this.previews[key];
            }
         }">
        @foreach($slots as $i => $slot)
            <div class="rounded-xl border border-gray-200 bg-slate-50/80 p-3">
                <div class="text-xs font-bold text-gray-700 mb-1">{{ $slot['label'] }}</div>
                <p class="text-[10px] text-gray-400 mb-2">{{ $slot['hint'] }}</p>
                <label class="flex flex-col items-center justify-center min-h-[140px] border-2 border-dashed border-gray-200 rounded-lg bg-white hover:border-indigo-400 hover:bg-indigo-50/40 cursor-pointer transition relative overflow-hidden">
                    <template x-if="!previews.{{ $slot['field'] }}">
                        <div class="text-center p-3">
                            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="mt-2 block text-xs font-semibold text-indigo-600">Upload</span>
                        </div>
                    </template>
                    <template x-if="previews.{{ $slot['field'] }}">
                        <img :src="previews.{{ $slot['field'] }}" class="absolute inset-0 w-full h-full object-contain p-2 bg-white" alt="">
                    </template>
                    <input type="file" name="{{ $slot['field'] }}" accept="image/jpeg,image/png,image/webp,image/jpg" class="sr-only"
                           @change="setPreview('{{ $slot['field'] }}', $event)">
                </label>
                @isset($product)
                    @if($product->{$slot['field']})
                        <label class="mt-2 flex items-center gap-1.5 text-[11px] text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_{{ $slot['field'] }}" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            Remove this picture
                        </label>
                    @endif
                @endisset
                @error($slot['field']) <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
            </div>
        @endforeach
    </div>
</div>
