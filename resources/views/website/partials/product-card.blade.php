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
