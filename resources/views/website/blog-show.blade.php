@extends('website.layout')
@php
    $colorMap = [
        'blue' => 'text-blue-600 bg-blue-50',
        'emerald' => 'text-emerald-700 bg-emerald-50',
        'amber' => 'text-amber-700 bg-amber-50',
        'rose' => 'text-rose-700 bg-rose-50',
        'violet' => 'text-violet-700 bg-violet-50',
        'slate' => 'text-slate-600 bg-slate-100',
    ];
@endphp
@section('title', $blog->title.' — '.($settings->store_name ?? 'GAGET STORE'))
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <p class="text-xs text-slate-400 mb-5">
        <a href="{{ route('home') }}" class="hover:text-slate-600">Home</a>
        <span class="mx-1">/</span>
        <a href="{{ route('website.blogs') }}" class="hover:text-slate-600">Blog</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">{{ $blog->title }}</span>
    </p>

    <div class="grid lg:grid-cols-12 gap-8">
        <article class="lg:col-span-8">
            @if($blog->category)
                <span class="text-[10px] font-bold uppercase tracking-wider {{ $colorMap[$blog->category->color ?? 'blue'] ?? $colorMap['blue'] }} px-2 py-0.5 rounded">{{ $blog->category->name }}</span>
            @endif
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-snug">{{ $blog->title }}</h1>
            <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-400">
                @if($blog->author_name)<span>By {{ $blog->author_name }}</span>@endif
                <span>{{ optional($blog->published_at)->format('M d, Y') }}</span>
                <span>{{ $blog->viewsLabel() }}</span>
            </div>

            <img src="{{ $blog->coverUrl() }}" alt="" class="mt-6 w-full rounded-2xl object-cover max-h-[420px] border border-slate-100">

            @if($blog->excerpt)
                <p class="mt-6 text-base text-slate-600 leading-relaxed font-medium">{{ $blog->excerpt }}</p>
            @endif

            <div class="mt-6 text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $blog->body }}</div>

            @if(($relatedPosts ?? collect())->count())
                <section class="mt-10 pt-8 border-t border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900 mb-4">Related articles</h2>
                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach($relatedPosts as $rel)
                            <a href="{{ route('website.blog', $rel->slug) }}" class="rounded-xl border border-slate-100 overflow-hidden hover:border-blue-100 transition">
                                <img src="{{ $rel->coverUrl() }}" alt="" class="h-28 w-full object-cover bg-slate-50">
                                <div class="p-3">
                                    <p class="text-xs font-semibold text-slate-800 line-clamp-2">{{ $rel->title }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>

        <aside class="lg:col-span-4 space-y-5">
            @if(($blogCategories ?? collect())->count())
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Categories</h3>
                <ul class="space-y-2">
                    @foreach($blogCategories as $cat)
                        <li>
                            <a href="{{ route('website.blogs', ['category' => $cat->slug]) }}" class="flex justify-between text-sm text-slate-600 hover:text-blue-600">
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs text-slate-400">({{ $cat->blogs_count }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(($popularPosts ?? collect())->count())
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Popular Posts</h3>
                <ul class="space-y-3">
                    @foreach($popularPosts as $pop)
                        <li>
                            <a href="{{ route('website.blog', $pop->slug) }}" class="flex gap-3 group">
                                <img src="{{ $pop->coverUrl() }}" alt="" class="w-14 h-14 rounded-lg object-cover bg-slate-50 shrink-0">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-slate-800 group-hover:text-blue-600 line-clamp-2">{{ $pop->title }}</p>
                                    <p class="text-[11px] text-slate-400 mt-1">{{ optional($pop->published_at)->format('M d, Y') }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <a href="{{ route('website.blogs') }}" class="block text-center text-sm font-semibold text-blue-600 hover:underline">← Back to Blog</a>
        </aside>
    </div>
</div>
@endsection
