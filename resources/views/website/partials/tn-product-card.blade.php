@php
    $ws = app(\App\Services\WebsiteService::class);
    $discount = ($product->original_price && $product->original_price > $product->selling_price)
        ? round((($product->original_price - $product->selling_price) / $product->original_price) * 100)
        : 0;
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

    $cartItem = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->selling_price,
        'image' => $img,
    ];
@endphp

<div class="tn-product-card">
    @if($discount > 0)
        <span class="tn-product-discount">-{{ $discount }}%</span>
    @endif

    <a href="{{ route('website.product', $product) }}" class="tn-product-img" aria-label="{{ $product->name }}">
        <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy">
    </a>

    <div class="tn-product-meta">
        <a href="{{ route('website.product', $product) }}" class="tn-product-name">{{ $product->name }}</a>

        <div class="tn-product-price-row">
            <span class="tn-product-price">{{ $ws->formatPrice($product->selling_price, $settings) }}</span>
            @if($product->original_price && $product->original_price > $product->selling_price)
                <span class="tn-product-old">{{ $ws->formatPrice($product->original_price, $settings) }}</span>
            @endif
        </div>

        @if(($product->rating ?? 0) > 0)
            <div class="tn-product-stars" aria-label="Rating {{ $product->rating }}">
                @for($i = 1; $i <= 5; $i++)
                    <span class="tn-product-star {{ $i > round($product->rating) ? 'empty' : '' }}">★</span>
                @endfor
            </div>
        @endif

        <button type="button"
                class="tn-product-add"
                data-add-to-cart='@json($cartItem)'
                data-qty="1"
                data-open-cart="1">
            Add to cart
        </button>
    </div>
</div>

