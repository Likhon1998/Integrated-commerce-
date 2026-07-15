{{-- Expects optional $product when editing --}}
@php
    $slots = [
        ['field' => 'image', 'label' => 'Picture 1 (Main)', 'hint' => 'Primary store photo'],
        ['field' => 'image_2', 'label' => 'Picture 2', 'hint' => 'Gallery thumbnail'],
        ['field' => 'image_3', 'label' => 'Picture 3', 'hint' => 'Gallery thumbnail'],
    ];
@endphp
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3"
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
    @foreach($slots as $slot)
        <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-2.5">
            <div class="text-xs font-medium text-slate-700 mb-0.5">{{ $slot['label'] }}</div>
            <p class="text-[10px] text-slate-400 mb-2">{{ $slot['hint'] }}</p>
            <label class="flex flex-col items-center justify-center min-h-[120px] border-2 border-dashed border-slate-200 rounded-lg bg-white hover:border-blue-400 hover:bg-blue-50/30 cursor-pointer transition relative overflow-hidden">
                <template x-if="!previews.{{ $slot['field'] }}">
                    <div class="text-center p-3">
                        <svg class="mx-auto h-7 w-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="mt-1.5 block text-xs font-medium text-blue-600">Upload</span>
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
                    <label class="mt-1.5 flex items-center gap-1.5 text-[11px] text-red-600 cursor-pointer">
                        <input type="checkbox" name="remove_{{ $slot['field'] }}" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        Remove
                    </label>
                @endif
            @endisset
            @error($slot['field']) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    @endforeach
</div>
