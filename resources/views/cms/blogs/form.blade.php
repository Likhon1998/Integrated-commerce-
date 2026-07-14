<x-cms-layout title="{{ $blog->exists ? 'Edit blog post' : 'New blog post' }}" subtitle="Published posts appear on the website blog and homepage." previewUrl="{{ route('website.blogs') }}">
    <form method="POST" action="{{ $blog->exists ? route('cms.blogs.update', $blog) : route('cms.blogs.store') }}" enctype="multipart/form-data" class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        @if($blog->exists) @method('PUT') @endif
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">Title</label>
            <input name="title" value="{{ old('title', $blog->title) }}" class="mt-1 w-full rounded-xl border-slate-200" required>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Slug</label>
                <input name="slug" value="{{ old('slug', $blog->slug) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Author</label>
                <input name="author_name" value="{{ old('author_name', $blog->author_name) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
        </div>
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">Excerpt</label>
            <textarea name="excerpt" rows="2" class="mt-1 w-full rounded-xl border-slate-200">{{ old('excerpt', $blog->excerpt) }}</textarea>
        </div>
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">Body</label>
            <textarea name="body" rows="12" class="mt-1 w-full rounded-xl border-slate-200">{{ old('body', $blog->body) }}</textarea>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Cover image</label>
                <input type="file" name="cover" accept="image/*" class="mt-1 w-full rounded-xl border-slate-200 text-sm">
                @if($blog->cover_image)<img src="{{ public_storage_url($blog->cover_image) }}" class="mt-2 h-24 rounded-xl object-cover" alt="">@endif
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Publish date</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($blog->published_at)->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-xl border-slate-200">
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_published" value="1" class="rounded" @checked(old('is_published', $blog->is_published))> Published on website</label>
        <div class="flex justify-end gap-2">
            <a href="{{ route('cms.blogs.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-bold text-slate-600">Cancel</a>
            <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">Save post</button>
        </div>
    </form>
</x-cms-layout>
