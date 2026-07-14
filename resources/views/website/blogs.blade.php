@extends('website.layout')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Blog</h1>
    <p class="mt-2 text-slate-500">News, tips, and updates from {{ $settings->store_name }}.</p>
    <div class="mt-8 grid gap-5 md:grid-cols-3">
        @forelse($blogs as $post)
            <a href="{{ route('website.blog', $post->slug) }}" class="rounded-2xl border border-slate-100 overflow-hidden bg-white shadow-sm no-underline hover:border-indigo-200 transition">
                @if($post->cover_image)
                    <img src="{{ public_storage_url($post->cover_image) }}" alt="" class="h-44 w-full object-cover">
                @else
                    <div class="h-44 bg-slate-100"></div>
                @endif
                <div class="p-4">
                    <div class="text-xs text-slate-400">{{ optional($post->published_at)->format('M d, Y') }}</div>
                    <div class="mt-1 font-bold text-slate-900">{{ $post->title }}</div>
                    <p class="mt-1 text-sm text-slate-500 line-clamp-3">{{ $post->excerpt }}</p>
                </div>
            </a>
        @empty
            <p class="md:col-span-3 text-center text-slate-400 py-16">No blog posts yet.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $blogs->links() }}</div>
</div>
@endsection
