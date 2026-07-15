@extends('website.layout')
@php
    $settings = $settings ?? (object) [];
    $heroKicker = data_get($settings, 'blog_hero_kicker') ?: 'OUR BLOG';
    $heroTitle = data_get($settings, 'blog_hero_title') ?: 'News & Articles';
    $heroSub = data_get($settings, 'blog_hero_subtitle')
        ?: 'Stay updated with the latest tech news, product reviews, and buying guides from '.data_get($settings, 'store_name', 'GAGET STORE').'.';
    $heroImage = data_get($settings, 'blog_hero_image')
        ? public_storage_url(data_get($settings, 'blog_hero_image'))
        : 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1600&q=80';
    $newsletterTitle = data_get($settings, 'blog_newsletter_title') ?: 'Subscribe to Our Newsletter';
    $newsletterText = data_get($settings, 'blog_newsletter_text') ?: 'Get the latest deals and tech news delivered to your inbox.';
    $featuredPost = $featuredPost ?? null;
    $blogCategories = $blogCategories ?? collect();
    $popularPosts = $popularPosts ?? collect();
    $blogSearch = $blogSearch ?? null;
    $activeBlogCategory = $activeBlogCategory ?? null;
    $colorMap = [
        'blue' => 'text-blue-600 bg-blue-50',
        'emerald' => 'text-emerald-700 bg-emerald-50',
        'amber' => 'text-amber-700 bg-amber-50',
        'rose' => 'text-rose-700 bg-rose-50',
        'violet' => 'text-violet-700 bg-violet-50',
        'slate' => 'text-slate-600 bg-slate-100',
    ];
@endphp
@section('title', 'Blog — '.($settings->store_name ?? 'GAGET STORE'))
@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden border-b border-slate-100">
    <div class="absolute inset-0">
        <img src="{{ $heroImage }}" alt="" class="h-full w-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-r from-slate-100 via-slate-50/95 to-slate-50/70"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 py-14 sm:py-16">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-blue-600">{{ $heroKicker }}</p>
        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">{{ $heroTitle }}</h1>
        <p class="mt-3 max-w-xl text-sm sm:text-base text-slate-600 leading-relaxed">{{ $heroSub }}</p>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="grid lg:grid-cols-12 gap-8">
        {{-- Main column --}}
        <div class="lg:col-span-8 space-y-8">
            {{-- Featured --}}
            @if($featuredPost && !$blogSearch && !$activeBlogCategory)
            <article class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="grid md:grid-cols-2 gap-0">
                    <a href="{{ route('website.blog', $featuredPost->slug) }}" class="block bg-slate-50 min-h-[220px]">
                        <img src="{{ $featuredPost->coverUrl() }}" alt="" class="h-full w-full object-cover min-h-[220px] max-h-[320px]">
                    </a>
                    <div class="p-6 sm:p-8 flex flex-col justify-center">
                        <span class="inline-flex w-fit text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">Featured</span>
                        <h2 class="mt-3 text-xl sm:text-2xl font-bold text-slate-900 leading-snug">
                            <a href="{{ route('website.blog', $featuredPost->slug) }}" class="hover:text-blue-600">{{ $featuredPost->title }}</a>
                        </h2>
                        @if($featuredPost->excerpt)
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed line-clamp-3">{{ $featuredPost->excerpt }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                            @if($featuredPost->author_name)<span>By {{ $featuredPost->author_name }}</span>@endif
                            <span>{{ optional($featuredPost->published_at)->format('M d, Y') }}</span>
                            <span>{{ $featuredPost->viewsLabel() }}</span>
                        </div>
                        <a href="{{ route('website.blog', $featuredPost->slug) }}"
                           class="mt-5 inline-flex items-center gap-2 w-fit bg-blue-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-blue-700 transition">
                            Read More
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </article>
            @endif

            @if($blogSearch || $activeBlogCategory)
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-600">
                        @if($blogSearch) Results for “<strong>{{ $blogSearch }}</strong>” @endif
                        @if($activeBlogCategory)
                            @php $cat = $blogCategories->firstWhere('slug', $activeBlogCategory); @endphp
                            Category: <strong>{{ $cat->name ?? $activeBlogCategory }}</strong>
                        @endif
                    </p>
                    <a href="{{ route('website.blogs') }}" class="text-xs font-semibold text-blue-600">Clear filters</a>
                </div>
            @endif

            {{-- Grid --}}
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @forelse($blogs as $post)
                    @continue($featuredPost && !$blogSearch && !$activeBlogCategory && $post->id === $featuredPost->id && $blogs->currentPage() === 1)
                    <article class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden flex flex-col hover:border-blue-100 transition">
                        <a href="{{ route('website.blog', $post->slug) }}" class="block bg-slate-50 aspect-[16/10] overflow-hidden">
                            <img src="{{ $post->coverUrl() }}" alt="" class="h-full w-full object-cover hover:scale-105 transition duration-300">
                        </a>
                        <div class="p-4 flex flex-col flex-1">
                            @if($post->category)
                                <span class="text-[10px] font-bold uppercase tracking-wider {{ $colorMap[$post->category->color ?? 'blue'] ?? $colorMap['blue'] }} px-2 py-0.5 rounded w-fit">
                                    {{ $post->category->name }}
                                </span>
                            @endif
                            <h3 class="mt-2 text-sm font-bold text-slate-900 leading-snug line-clamp-2">
                                <a href="{{ route('website.blog', $post->slug) }}" class="hover:text-blue-600">{{ $post->title }}</a>
                            </h3>
                            <p class="mt-1.5 text-xs text-slate-500 line-clamp-2 flex-1">{{ $post->excerpt }}</p>
                            <div class="mt-3 flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ optional($post->published_at)->format('M d, Y') }} · {{ $post->viewsLabel() }}</span>
                            </div>
                            <a href="{{ route('website.blog', $post->slug) }}" class="mt-3 text-xs font-semibold text-blue-600 inline-flex items-center gap-1 hover:gap-1.5 transition-all">
                                Read More
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="sm:col-span-2 xl:col-span-3 text-center text-slate-400 py-16 text-sm">No blog posts yet. Add posts from Admin → CMS → Blogs.</p>
                @endforelse
            </div>

            <div>{{ $blogs->links() }}</div>
        </div>

        {{-- Sidebar --}}
        <aside class="lg:col-span-4 space-y-5">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <form action="{{ route('website.blogs') }}" method="GET" class="flex gap-2">
                    @if($activeBlogCategory)<input type="hidden" name="category" value="{{ $activeBlogCategory }}">@endif
                    <input type="search" name="q" value="{{ $blogSearch }}" placeholder="Search articles..."
                           class="flex-1 rounded-lg border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit" class="shrink-0 w-10 h-10 rounded-lg bg-slate-900 text-white flex items-center justify-center hover:bg-slate-800" title="Search">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>
            </div>

            @if($blogCategories->count())
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Categories</h3>
                <ul class="space-y-2">
                    @foreach($blogCategories as $cat)
                        <li>
                            <a href="{{ route('website.blogs', ['category' => $cat->slug]) }}"
                               class="flex items-center justify-between text-sm {{ ($activeBlogCategory ?? '') === $cat->slug ? 'text-blue-600 font-semibold' : 'text-slate-600 hover:text-blue-600' }}">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md {{ $colorMap[$cat->color ?? 'blue'] ?? $colorMap['blue'] }} flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($cat->name, 0, 1)) }}</span>
                                    {{ $cat->name }}
                                </span>
                                <span class="text-xs text-slate-400">({{ $cat->blogs_count }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($popularPosts->count())
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Popular Posts</h3>
                <ul class="space-y-3">
                    @foreach($popularPosts as $pop)
                        <li>
                            <a href="{{ route('website.blog', $pop->slug) }}" class="flex gap-3 group">
                                <img src="{{ $pop->coverUrl() }}" alt="" class="w-14 h-14 rounded-lg object-cover bg-slate-50 shrink-0">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-slate-800 group-hover:text-blue-600 line-clamp-2 leading-snug">{{ $pop->title }}</p>
                                    <p class="text-[11px] text-slate-400 mt-1">{{ optional($pop->published_at)->format('M d, Y') }}</p>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900">{{ $newsletterTitle }}</h3>
                <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">{{ $newsletterText }}</p>
                @if(session('newsletter_success'))
                    <p class="mt-3 text-xs font-semibold text-emerald-600">{{ session('newsletter_success') }}</p>
                @endif
                <form action="{{ route('website.newsletter') }}" method="POST" class="mt-3 space-y-2">
                    @csrf
                    <input type="email" name="email" required placeholder="Your email address"
                           class="w-full rounded-lg border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit" class="w-full h-10 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Subscribe
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>
@endsection
