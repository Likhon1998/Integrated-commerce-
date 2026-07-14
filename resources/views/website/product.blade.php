@extends('website.layout')
@php
    $ws = app(\App\Services\WebsiteService::class);
    $images = $ws->productImageUrls($product);
    $img = $images[0];
    $reviews = $reviews ?? collect();
    $discountPct = ($product->original_price && $product->original_price > $product->selling_price)
        ? (int) round((1 - ($product->selling_price / $product->original_price)) * 100)
        : 0;
@endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
    <p class="text-xs text-slate-400 mb-5">
        <a href="{{ route('home') }}" class="hover:text-slate-600">Home</a>
        <span class="mx-1">/</span>
        @if($product->category)
            <a href="{{ route('website.shop', ['category' => $product->category_id]) }}" class="hover:text-slate-600">{{ $product->category->name }}</a>
            <span class="mx-1">/</span>
        @else
            <a href="{{ route('website.shop') }}" class="hover:text-slate-600">Shop</a>
            <span class="mx-1">/</span>
        @endif
        <span class="text-slate-600">{{ $product->name }}</span>
    </p>

    <div class="grid lg:grid-cols-2 gap-8 lg:gap-10"
         x-data="{ active: 0, images: @js($images) }">
        {{-- Gallery --}}
        <div class="flex gap-3">
            @if(count($images) > 1)
                <div class="flex flex-col gap-2 w-14 sm:w-16 shrink-0">
                    @foreach($images as $i => $url)
                        <button type="button"
                                @click="active = {{ $i }}"
                                :class="active === {{ $i }} ? 'border-blue-600' : 'border-slate-200 hover:border-slate-300'"
                                class="aspect-square rounded-lg border bg-white overflow-hidden p-1 transition">
                            <img src="{{ $url }}" alt="" class="w-full h-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif
            <div class="flex-1 bg-slate-50 rounded-xl p-5 flex flex-col items-center justify-center border border-slate-100 min-h-[280px] sm:min-h-[360px]">
                <img :src="images[active]" alt="{{ $product->name }}" class="max-h-[340px] w-full object-contain">
                @if(count($images) > 1)
                    <p class="mt-3 text-[11px] text-slate-400">Click a thumbnail to switch photos</p>
                @endif
            </div>
        </div>

        {{-- Buy panel --}}
        <div class="lg:pt-1">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @if($product->is_new_arrival)
                    <span class="text-[10px] font-semibold uppercase tracking-wide bg-blue-600 text-white px-2 py-0.5 rounded">New</span>
                @endif
                @if($product->brand_name || $product->brand)
                    <span class="text-xs font-medium text-blue-600">{{ $product->brand_name ?? $product->brand?->name }}</span>
                @elseif($product->category)
                    <span class="text-xs font-medium text-blue-600">{{ $product->category->name }}</span>
                @endif
            </div>

            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 tracking-tight mb-3">{{ $product->name }}</h1>

            @if($product->rating > 0 || $reviews->isNotEmpty())
                <div class="flex items-center gap-1 mb-3">
                    @php $stars = $product->rating > 0 ? round($product->rating) : (int) round($reviews->avg('rating')); @endphp
                    @for($i=1;$i<=5;$i++)<span class="text-sm {{ $i<=$stars?'text-amber-400':'text-slate-200' }}">★</span>@endfor
                    <span class="text-xs text-slate-500 ml-1.5">({{ $product->review_count ?: $reviews->count() }} reviews)</span>
                </div>
            @endif

            <div class="flex flex-wrap items-baseline gap-2.5 mb-4 pb-4 border-b border-slate-100">
                <span class="text-xl font-semibold text-blue-600">{{ $ws->formatPrice($product->selling_price, $settings) }}</span>
                @if($product->original_price && $product->original_price > $product->selling_price)
                    <span class="text-sm text-slate-400 line-through">{{ $ws->formatPrice($product->original_price, $settings) }}</span>
                    @if($discountPct > 0)
                        <span class="text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded">{{ $discountPct }}% OFF</span>
                    @endif
                @endif
            </div>

            @if($product->short_description)
                <div class="text-sm text-slate-600 leading-relaxed mb-5 whitespace-pre-line max-w-prose">{{ $product->short_description }}</div>
            @endif

            <p class="text-sm mb-5 {{ $product->stock_quantity > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                @if($product->stock_quantity > 0)
                    <span class="inline-flex items-center gap-1.5 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        In Stock ({{ $product->stock_quantity }} available)
                    </span>
                @else
                    Out of Stock
                @endif
            </p>

            @if($product->stock_quantity > 0)
                <div class="flex flex-wrap items-center gap-2.5" x-data="{ qty: 1 }">
                    <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden bg-white h-10">
                        <button type="button" @click="qty = Math.max(1, qty - 1)" class="w-9 h-full text-sm font-medium text-slate-500 hover:bg-slate-50">−</button>
                        <span class="w-8 text-center text-sm font-medium text-slate-800" x-text="qty"></span>
                        <button type="button" @click="qty = Math.min({{ (int) $product->stock_quantity }}, qty + 1)" class="w-9 h-full text-sm font-medium text-slate-500 hover:bg-slate-50">+</button>
                    </div>
                    <button type="button"
                            @click="for (let i = 0; i < qty; i++) addToCart({id:{{ $product->id }},name:@json($product->name),price:{{ (float) $product->selling_price }},image:@json($img)}}); cartOpen=true"
                            class="h-10 bg-blue-600 text-white px-5 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        Add to Cart
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($reviews->isNotEmpty())
    <section class="mt-10 pt-8 border-t border-slate-100">
        <h2 class="text-base font-semibold text-slate-900 mb-4">Customer reviews</h2>
        <div class="grid gap-3 md:grid-cols-2">
            @foreach($reviews as $review)
                <div class="rounded-xl border border-slate-100 bg-white p-4">
                    <div class="mb-1.5 text-amber-500 text-xs">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $review->rating)) }}</div>
                    <p class="text-sm text-slate-600 leading-relaxed">“{{ $review->body }}”</p>
                    <div class="mt-2 text-sm font-medium text-slate-800">{{ $review->customer_name }}</div>
                    @if($review->customer_title)
                        <div class="text-xs text-slate-400">{{ $review->customer_title }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
    @endif

    @if($related->count())
    <section class="mt-10 pt-8 border-t border-slate-100">
        <h2 class="text-base font-semibold text-slate-900 mb-4">Related products</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($related as $rel)
                @include('website.partials.product-card', ['product' => $rel])
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
