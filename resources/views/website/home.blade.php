@extends('website.layout')
@php $ws = app(\App\Services\WebsiteService::class); @endphp

@section('content')

@php $slideCount = max($heroSlides->count(), 1); @endphp
<section class="gaget-hero" x-data="{ slide: 0, total: {{ $slideCount }} }" x-init="if(total>1) setInterval(()=>{ slide=(slide+1)%total }, 8000)">
    @if($heroSlides->count() > 1)
        <button type="button" @click="slide=(slide-1+total)%total" class="gaget-hero-arrow left" aria-label="Prev">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" @click="slide=(slide+1)%total" class="gaget-hero-arrow right" aria-label="Next">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    @endif

    @forelse($heroSlides as $i => $slide)
        <div x-show="slide==={{ $i }}" x-cloak>
            <div class="gaget-hero-inner">
                <div class="gaget-hero-grid">
                    <div>
                        <span class="gaget-hero-badge">{{ strtoupper($slide->badge_text ?? 'NEW ARRIVAL') }}</span>
                        <h1 class="gaget-hero-title">{{ $slide->title }}</h1>
                        <p class="gaget-hero-desc">{{ $slide->description }}</p>
                        @if($slide->price_from)
                            <p class="gaget-hero-price">From <strong>{{ $ws->formatPrice($slide->price_from, $settings) }}</strong></p>
                        @endif
                        <div class="gaget-hero-btns">
                            <a href="{{ $slide->button_url ?? route('website.shop') }}" class="gaget-btn-primary">{{ $slide->button_text ?? 'Shop Now' }}</a>
                            <a href="{{ $slide->learn_more_url ?? route('website.shop') }}" class="gaget-btn-outline">Learn More</a>
                        </div>
                        @if($heroSlides->count() > 1)
                            <div class="gaget-hero-dots">
                                @foreach($heroSlides as $di => $ds)
                                    <button type="button" @click="slide={{ $di }}" class="gaget-hero-dot" :class="slide==={{ $di }}?'active':''"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="gaget-hero-visual">
                        @if($slide->image_path)
                            <img src="{{ public_storage_url($slide->image_path) }}" alt="{{ $slide->title }}">
                        @else
                            <img src="{{ config('website_assets.hero') }}" alt="{{ $slide->title }}">
                            <img src="{{ config('website_assets.hero_secondary') }}" alt="" class="hidden lg:block" style="max-height:300px">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="gaget-hero-inner">
            <div class="gaget-hero-grid">
                <div>
                    <span class="gaget-hero-badge">NEW ARRIVAL</span>
                    <h1 class="gaget-hero-title">iPhone 15 Pro Max</h1>
                    <p class="gaget-hero-desc">Titanium. So strong. So light. So Pro.</p>
                    <p class="gaget-hero-price">From <strong>$1,199.00</strong></p>
                    <div class="gaget-hero-btns">
                        <a href="{{ route('website.shop') }}" class="gaget-btn-primary">Shop Now</a>
                        <a href="{{ route('website.shop') }}" class="gaget-btn-outline">Learn More</a>
                    </div>
                    <div class="gaget-hero-dots"><span class="gaget-hero-dot active"></span><span class="gaget-hero-dot"></span><span class="gaget-hero-dot"></span></div>
                </div>
                <div class="gaget-hero-visual">
                    <img src="{{ config('website_assets.hero') }}" alt="iPhone 15 Pro Max">
                    <img src="{{ config('website_assets.hero_secondary') }}" alt="" class="hidden lg:block" style="max-height:300px">
                </div>
            </div>
        </div>
    @endforelse
</section>

{{-- Features --}}
<section class="gaget-features hidden md:block">
    <div class="gaget-features-grid">
        @forelse($features as $feature)
            <div class="gaget-feature-card">
                <div class="gaget-feature-icon">@include('website.partials.feature-icon', ['icon' => $feature->icon])</div>
                <div>
                    <p class="gaget-feature-title">{{ $feature->title }}</p>
                    @if($feature->subtitle)<p class="gaget-feature-sub">{{ $feature->subtitle }}</p>@endif
                </div>
            </div>
        @empty
            @foreach([['truck','Free Shipping','On all orders over $50'],['return','30-Day Returns','Hassle-free returns'],['lock','Secure Payments','100% secure payments'],['shield','1 Year Warranty','Product warranty'],['support','24/7 Support','Dedicated support']] as $f)
                <div class="gaget-feature-card">
                    <div class="gaget-feature-icon">@include('website.partials.feature-icon', ['icon'=>$f[0]])</div>
                    <div><p class="gaget-feature-title">{{ $f[1] }}</p><p class="gaget-feature-sub">{{ $f[2] }}</p></div>
                </div>
            @endforeach
        @endforelse
    </div>
</section>

{{-- Categories --}}
<section class="gaget-section">
    <div class="gaget-section-header">
        <h2 class="gaget-section-title">Shop By Category</h2>
        <a href="{{ route('website.shop') }}" class="gaget-section-link">View All Categories →</a>
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

{{-- Promo banners --}}
<section class="gaget-section" style="padding-top:0">
    <div class="gaget-promo-grid">
        @php $promoImgs = ['headphones','macbook','watch']; @endphp
        @forelse($promoBanners as $idx => $banner)
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
                <img src="{{ $banner->image_path ? public_storage_url($banner->image_path) : config('website_assets.promos.'.$promoImgs[$idx % 3]) }}" alt="" class="gaget-promo-img">
            </div>
        @empty
            <div class="gaget-promo gaget-promo-dark">
                <div class="gaget-promo-content">
                    <p class="gaget-promo-title">Summer Sale</p><p class="gaget-promo-sub">Up to 40% Off</p>
                    <a href="{{ route('website.shop') }}" class="gaget-promo-btn">Shop Now</a>
                </div>
                <img src="{{ config('website_assets.promos.headphones') }}" alt="" class="gaget-promo-img">
            </div>
            <div class="gaget-promo gaget-promo-light">
                <div class="gaget-promo-content">
                    <p class="gaget-promo-title">MacBook Air</p><p class="gaget-promo-sub">Supercharged by M3</p>
                    <p class="gaget-promo-price">From <strong>$1,099.00</strong></p>
                    <a href="{{ route('website.shop') }}" class="gaget-promo-btn-text">Shop Now</a>
                </div>
                <img src="{{ config('website_assets.promos.macbook') }}" alt="" class="gaget-promo-img">
            </div>
            <div class="gaget-promo gaget-promo-dark">
                <div class="gaget-promo-content">
                    <p class="gaget-promo-title">Best Deals</p><p class="gaget-promo-sub">Smartwatches</p>
                    <p class="gaget-promo-price">From <strong>$99.00</strong></p>
                    <a href="{{ route('website.shop') }}" class="gaget-promo-btn">Shop Now</a>
                </div>
                <img src="{{ config('website_assets.promos.watch') }}" alt="" class="gaget-promo-img">
            </div>
        @endforelse
    </div>
</section>

{{-- Best Sellers --}}
<section class="gaget-bestsellers-bg">
    <div class="gaget-section">
        <div class="gaget-section-header">
            <h2 class="gaget-section-title">Best Sellers</h2>
            <a href="{{ route('website.shop', ['filter'=>'bestsellers']) }}" class="gaget-section-link">View All Products →</a>
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
                <p class="text-center text-gray-500 py-10 col-span-5">Add products in Inventory → Products to show them here.</p>
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
                <div class="mb-2 text-amber-500 text-sm">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $review->rating)) }}</div>
                <p class="text-sm text-slate-600 leading-relaxed">“{{ $review->body }}”</p>
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
        <a href="{{ route('website.blogs') }}" class="gaget-section-link">View all →</a>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        @foreach($latestBlogs as $post)
            <a href="{{ route('website.blog', $post->slug) }}" class="rounded-2xl border border-slate-100 overflow-hidden bg-white shadow-sm no-underline hover:border-blue-100 transition">
                <img src="{{ $post->coverUrl() }}" alt="" class="h-40 w-full object-cover bg-slate-50">
                <div class="p-4">
                    @if($post->category)
                        <div class="text-[10px] font-bold uppercase tracking-wider text-blue-600">{{ $post->category->name }}</div>
                    @endif
                    <div class="text-xs text-slate-400 mt-0.5">{{ optional($post->published_at)->format('M d, Y') }} · {{ $post->viewsLabel() }}</div>
                    <div class="mt-1 font-bold text-slate-900 text-sm">{{ $post->title }}</div>
                    <p class="mt-1 text-sm text-slate-500 line-clamp-2">{{ $post->excerpt }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

@endsection
