<x-cms-layout
    title="{{ $slide->exists ? 'Edit slide' : 'New home slide' }}"
    subtitle="Appears in the homepage hero carousel when Active."
    previewUrl="{{ route('home') }}"
>
    <form method="POST"
          action="{{ $slide->exists ? route('cms.slides.update', $slide) : route('cms.slides.store') }}"
          enctype="multipart/form-data"
          class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        @if($slide->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="text-xs font-bold uppercase text-slate-500">Title</label>
                <input name="title" value="{{ old('title', $slide->title) }}" class="mt-1 w-full rounded-xl border-slate-200" required>
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Badge text</label>
                <input name="badge_text" value="{{ old('badge_text', $slide->badge_text) }}" class="mt-1 w-full rounded-xl border-slate-200" placeholder="NEW ARRIVAL">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Price from</label>
                <input type="number" step="0.01" name="price_from" value="{{ old('price_from', $slide->price_from) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs font-bold uppercase text-slate-500">Description</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border-slate-200">{{ old('description', $slide->description) }}</textarea>
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Button text</label>
                <input name="button_text" value="{{ old('button_text', $slide->button_text) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Button URL</label>
                <input name="button_url" value="{{ old('button_url', $slide->button_url) }}" class="mt-1 w-full rounded-xl border-slate-200" placeholder="/shop">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Learn more URL</label>
                <input name="learn_more_url" value="{{ old('learn_more_url', $slide->learn_more_url) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Sort order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order ?? 0) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div class="md:col-span-2">
                <label class="text-xs font-bold uppercase text-slate-500">Image</label>
                <input type="file" name="image" accept="image/*" class="mt-1 w-full rounded-xl border-slate-200 text-sm">
                @if($slide->image_path)
                    <img src="{{ public_storage_url($slide->image_path) }}" class="mt-2 h-28 rounded-xl object-cover" alt="">
                @endif
            </div>
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $slide->is_active))>
                Active on website
            </label>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('cms.slides.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600">Cancel</a>
            <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">Save slide</button>
        </div>
    </form>
</x-cms-layout>
