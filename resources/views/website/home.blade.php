@extends('website.layout')
@php $ws = app(\App\Services\WebsiteService::class); @endphp

@section('content')

<section class="gaget-hero" x-data="{ slide: 0, total: {{ max($heroSlides->count(), 1) }} }"
         @if($heroSlides->count() > 1) x-init="setInterval(()=>{ slide=(slide+1)%total }, 6000)" @endif>
    @if($heroSlides->count() > 1)
        <button type="button" @click="slide=(slide-1+total)%total" class="gaget-hero-arrow left" aria-label="Prev">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" @click="slide=(slide+1)%total" class="gaget-hero-arrow right" aria-label="Next">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    @endif

    @forelse($heroSlides as $i => $slide)
        @php
            $posterUrl = $slide->image_path
                ? public_storage_url($slide->image_path)
                : config('website_assets.hero');
            $link = $slide->button_url ?: null;
        @endphp
        <div class="gaget-poster-slide" x-show="slide==={{ $i }}" @if($i > 0) x-cloak @endif>
            @if($link)
                <a href="{{ $link }}" class="gaget-poster-link" aria-label="{{ $slide->title }}">
                    <img src="{{ $posterUrl }}" alt="{{ $slide->title }}" class="gaget-poster-img">
                </a>
            @else
                <div class="gaget-poster-link">
                    <img src="{{ $posterUrl }}" alt="{{ $slide->title }}" class="gaget-poster-img">
                </div>
            @endif
        </div>
    @empty
        <div class="gaget-poster-slide gaget-poster-empty">
            <div class="gaget-poster-empty-inner" aria-hidden="true"></div>
        </div>
    @endforelse

    @if($heroSlides->count() > 1)
        <div class="gaget-hero-dots gaget-poster-dots">
            @foreach($heroSlides as $di => $ds)
                <button type="button" @click="slide={{ $di }}" class="gaget-hero-dot" :class="slide==={{ $di }}?'active':''" aria-label="Poster {{ $di + 1 }}"></button>
            @endforeach
        </div>
    @endif
</section>

{{-- Features (CMS → Landing Page) --}}
@if($features->isNotEmpty())
<section class="gaget-features hidden md:block">
    <div class="gaget-features-grid" style="grid-template-columns: repeat({{ min($features->count(), 5) }}, 1fr);">
        @foreach($features as $feature)
            <div class="gaget-feature-card">
                <div class="gaget-feature-icon">@include('website.partials.feature-icon', ['icon' => $feature->icon])</div>
                <div>
                    <p class="gaget-feature-title">{{ $feature->title }}</p>
                    @if($feature->subtitle)<p class="gaget-feature-sub">{{ $feature->subtitle }}</p>@endif
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- Categories --}}
<section class="gaget-section">
    <div class="gaget-section-header">
        <h2 class="gaget-section-title">Shop By Category</h2>
        <a href="{{ route('website.shop') }}" class="gaget-section-link">View All Categories ΓåÆ</a>
    </div>
    <div class="gaget-cat-grid">
        @forelse($categories as $category)
            <a href="{{ route('website.category', $category->slug ?? $category->id) }}" class="gaget-cat-card">
                <div class="gaget-cat-img">
                    <img src="{{ $ws->categoryImageUrl($category) }}" alt="{{ $category->name }}">
                </div>
                <p class="gaget-cat-name">{{ $category->name }}</p>
                <p class="gaget-cat-count">{{ $category->product_count_label ?? ($category->products_count.'+ Products') }}</p>
            </a>
        @empty
            @foreach([['Smartphones','120+ Products','smartphones'],['Laptops','85+ Products','laptops'],['Headphones','200+ Products','headphones'],['Smartwatches','60+ Products','smartwatches'],['Cameras','45+ Products','cameras'],['Accessories','300+ Products','accessories'],['Tablets','50+ Products','tablets'],['Gaming','90+ Products','gaming']] as $c)
                <a href="{{ route('website.shop') }}" class="gaget-cat-card">
                    <div class="gaget-cat-img"><img src="{{ config('website_assets.categories.'.$c[2]) }}" alt="{{ $c[0] }}"></div>
                    <p class="gaget-cat-name">{{ $c[0] }}</p>
                    <p class="gaget-cat-count">{{ $c[1] }}</p>
                </a>
            @endforeach
        @endforelse
    </div>
</section>

{{-- Promo banners (CMS → Landing Page) --}}
@if($promoBanners->isNotEmpty())
<section class="gaget-section" style="padding-top:0">
    <div class="gaget-promo-grid">
        @foreach($promoBanners as $banner)
            <div class="gaget-promo {{ $banner->theme === 'light' ? 'gaget-promo-light' : 'gaget-promo-dark' }}">
                <div class="gaget-promo-content">
                    <p class="gaget-promo-title">{{ $banner->title }}</p>
                    @if($banner->subtitle)<p class="gaget-promo-sub">{{ $banner->subtitle }}</p>@endif
                    @if($banner->price_from)<p class="gaget-promo-price">From <strong>{{ $ws->formatPrice($banner->price_from, $settings) }}</strong></p>@endif
                    @if($banner->theme === 'light')
                        <a href="{{ $banner->button_url ?? route('website.shop') }}" class="gaget-promo-btn-text">{{ $banner->button_text ?? 'Shop Now' }}</a>
                    @else
                        <a href="{{ $banner->button_url ?? route('website.shop') }}" class="gaget-promo-btn">{{ $banner->button_text ?? 'Shop Now' }}</a>
                    @endif
                </div>
                @if($banner->image_path)
                    <img src="{{ public_storage_url($banner->image_path) }}" alt="{{ $banner->title }}" class="gaget-promo-img">
                @endif
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- Best Sellers --}}
<section class="gaget-bestsellers-bg">
    <div class="gaget-section">
        <div class="gaget-section-header">
            <h2 class="gaget-section-title">Best Sellers</h2>
            <a href="{{ route('website.shop', ['filter'=>'bestsellers']) }}" class="gaget-section-link">View All Products ΓåÆ</a>
        </div>
        @php $items = $bestSellers; $perPage = 5; $pages = max(1, (int) ceil($items->count() / $perPage)); @endphp
        <div class="gaget-carousel-wrap" x-data="{ page: 0, pages: {{ $pages }} }">
            @if($items->count() > $perPage)
                <button type="button" @click="page=(page-1+pages)%pages" class="gaget-carousel-arrow prev"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                <button type="button" @click="page=(page+1)%pages" class="gaget-carousel-arrow next"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
            @endif
            @for($p = 0; $p < $pages; $p++)
                <div x-show="page==={{ $p }}" x-cloak class="gaget-product-grid-5">
                    @foreach($items->slice($p * $perPage, $perPage) as $product)
                        @include('website.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @endfor
            @if($items->isEmpty())
                <p class="text-center text-gray-500 py-10 col-span-5">Add products in Inventory ΓåÆ Products to show them here.</p>
            @endif
        </div>
    </div>
</section>

{{-- Brands --}}
<section id="brands" class="gaget-brands">
    <p class="gaget-brands-text">{{ $settings->trusted_by_text ?? 'Trusted by 10,000+ customers worldwide' }}</p>
    <div class="gaget-brands-row">
        @forelse($brands as $brand)
            <a href="{{ route('website.brand', \Illuminate\Support\Str::slug($brand->name)) }}" class="gaget-brand-logo no-underline">
                @if($brand->logo_path)<img src="{{ public_storage_url($brand->logo_path) }}" alt="{{ $brand->name }}">@else{{ $brand->name }}@endif
            </a>
        @empty
            @foreach(['Apple','Samsung','Sony','Bose','Canon','Dell','Xiaomi'] as $b)<span class="gaget-brand-logo">{{ $b }}</span>@endforeach
        @endforelse
    </div>
</section>

@if(($featuredReviews ?? collect())->isNotEmpty())
<section class="gaget-section">
    <div class="gaget-section-header">
        <h2 class="gaget-section-title">What customers say</h2>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($featuredReviews as $review)
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-2 text-amber-500 text-sm">{{ str_repeat('Γÿà', (int) $review->rating) }}{{ str_repeat('Γÿå', max(0, 5 - (int) $review->rating)) }}</div>
                <p class="text-sm text-slate-600 leading-relaxed">ΓÇ£{{ $review->body }}ΓÇ¥</p>
                <div class="mt-4 text-sm font-bold text-slate-900">{{ $review->customer_name }}</div>
                @if($review->customer_title)
                    <div class="text-xs text-slate-400">{{ $review->customer_title }}</div>
                @endif
            </div>
        @endforeach
    </div>
</section>
@endif

@if(($latestBlogs ?? collect())->isNotEmpty())
<section class="gaget-section">
    <div class="gaget-section-header">
        <h2 class="gaget-section-title">From the blog</h2>
        <a href="{{ route('website.blogs') }}" class="gaget-section-link">View all ΓåÆ</a>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($latestBlogs as $post)
            <a href="{{ route('website.blog', $post->slug) }}" class="rounded-2xl border border-slate-100 overflow-hidden bg-white shadow-sm no-underline hover:border-blue-100 transition">
                <img src="{{ $post->coverUrl() }}" alt="" class="h-40 w-full object-cover bg-slate-50">
                <div class="p-4">
                    @if($post->category)
                        <div class="text-[10px] font-bold uppercase tracking-wider text-blue-600">{{ $post->category->name }}</div>
                    @endif
                    <div class="text-xs text-slate-400 mt-0.5">{{ optional($post->published_at)->format('M d, Y') }} ┬╖ {{ $post->viewsLabel() }}</div>
                    <div class="mt-1 font-bold text-slate-900 text-sm">{{ $post->title }}</div>
                    <p class="mt-1 text-sm text-slate-500 line-clamp-2">{{ $post->excerpt }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

@endsection

