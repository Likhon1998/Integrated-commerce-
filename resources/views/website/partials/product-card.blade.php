@php
    $ws = app(\App\Services\WebsiteService::class);
    $discount = ($product->original_price && $product->original_price > $product->selling_price)
        ? round((($product->original_price - $product->selling_price) / $product->original_price) * 100) : 0;
    $img = $ws->productImageUrl($product);
    $catLabel = $product->category?->name ?? $product->brand_name ?? 'Electronics';
@endphp
<div class="gaget-product-card">
    @if($discount > 0)
        <span class="gaget-discount-badge">-{{ $discount }}%</span>
    @endif
    <button type="button" class="gaget-wishlist-btn" title="Wishlist">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
    </button>
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
    </div>
</div>
