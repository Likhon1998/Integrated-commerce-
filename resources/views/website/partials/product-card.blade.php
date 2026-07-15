@php
    $ws = app(\App\Services\WebsiteService::class);
    $discount = ($product->original_price && $product->original_price > $product->selling_price)
        ? round((($product->original_price - $product->selling_price) / $product->original_price) * 100) : 0;
    $img = $ws->productImageUrl($product);
    $catLabel = $product->category?->name ?? $product->brand_name ?? 'Electronics';
    $listItem = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->selling_price,
        'image' => $img,
        'url' => route('website.product', $product),
        'category' => $catLabel,
        'rating' => (float) ($product->rating ?? 0),
    ];
@endphp
<div class="gaget-product-card relative">
    @if($discount > 0)
        <span class="gaget-discount-badge">-{{ $discount }}%</span>
    @endif
    <div class="absolute top-2 right-2 z-10 flex flex-col gap-1.5">
        <button type="button" class="w-8 h-8 rounded-full bg-white/95 border border-slate-100 shadow-sm text-slate-400 hover:text-rose-500 inline-flex items-center justify-center"
                :class="inWishlist({{ $product->id }}) && '!text-rose-500'"
                @click.prevent="toggleWishlist(@js($listItem))" title="Wishlist">
            <svg class="w-4 h-4" :fill="inWishlist({{ $product->id }}) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </button>
        <button type="button" class="w-8 h-8 rounded-full bg-white/95 border border-slate-100 shadow-sm text-slate-400 hover:text-blue-600 inline-flex items-center justify-center"
                :class="inCompare({{ $product->id }}) && '!text-blue-600'"
                @click.prevent="toggleCompare(@js($listItem))" title="Compare">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </button>
    </div>
    <a href="{{ route('website.product', $product) }}" class="gaget-product-img">
        <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">
    </a>
    <div class="gaget-product-body">
        <p class="gaget-product-cat">{{ $catLabel }}</p>
        <a href="{{ route('website.product', $product) }}" class="gaget-product-name">{{ $product->name }}</a>
        <div class="gaget-stars">
            @for($i = 1; $i <= 5; $i++)
                <span class="gaget-star {{ $i > round($product->rating) ? 'gaget-star-empty' : '' }}">★</span>
            @endfor
            <span class="gaget-review-count">({{ number_format($product->review_count ?: 0) }})</span>
        </div>
        <div class="gaget-price-row">
            <span class="gaget-price-current">{{ $ws->formatPrice($product->selling_price, $settings) }}</span>
            @if($product->original_price && $product->original_price > $product->selling_price)
                <span class="gaget-price-old">{{ $ws->formatPrice($product->original_price, $settings) }}</span>
            @endif
        </div>
        <button type="button"
                class="mt-3 w-full rounded-xl bg-slate-900 text-white text-sm font-bold py-2.5 hover:bg-indigo-600 transition"
                @click.prevent="addToCart({id:{{ $product->id }},name:@json($product->name),price:{{ (float) $product->selling_price }},image:@json($img)}})">
            Add to cart
        </button>
    </div>
</div>
