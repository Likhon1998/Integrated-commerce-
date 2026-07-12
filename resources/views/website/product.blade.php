@extends('website.layout')
@php
    $ws = app(\App\Services\WebsiteService::class);
    $img = $ws->productImageUrl($product);
@endphp
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="grid lg:grid-cols-2 gap-10">
        <div class="bg-gray-50 rounded-2xl p-8 flex items-center justify-center">
            <img src="{{ $img }}" alt="{{ $product->name }}" class="max-h-[480px] object-contain">
        </div>
        <div>
            <p class="text-sm text-blue-600 font-semibold mb-2">{{ $product->category?->name ?? $product->brand_name }}</p>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-4">{{ $product->name }}</h1>
            @if($product->rating > 0)
                <div class="flex items-center gap-1 mb-4">
                    @for($i=1;$i<=5;$i++)<span class="{{ $i<=round($product->rating)?'text-yellow-400':'text-gray-200' }}">★</span>@endfor
                    <span class="text-sm text-gray-500 ml-2">({{ $product->review_count }} reviews)</span>
                </div>
            @endif
            <div class="flex items-center gap-3 mb-6">
                <span class="text-3xl font-bold text-blue-600">{{ $ws->formatPrice($product->selling_price, $settings) }}</span>
                @if($product->original_price && $product->original_price > $product->selling_price)
                    <span class="text-xl text-gray-400 line-through">{{ $ws->formatPrice($product->original_price, $settings) }}</span>
                @endif
            </div>
            @if($product->short_description)
                <p class="text-gray-600 mb-6">{{ $product->short_description }}</p>
            @endif
            <p class="text-sm mb-6 {{ $product->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $product->stock_quantity > 0 ? 'In Stock ('.$product->stock_quantity.' available)' : 'Out of Stock' }}
            </p>
            @if($product->stock_quantity > 0)
                <button @click="addToCart({id:{{ $product->id }},name:@json($product->name),price:{{ $product->selling_price }},image:@json($img)}})"
                        class="bg-blue-600 text-white px-10 py-4 rounded-xl font-bold text-lg hover:bg-blue-700 w-full md:w-auto">
                    Add to Cart
                </button>
            @endif
        </div>
    </div>

    @if($related->count())
    <section class="mt-16">
        <h2 class="text-2xl font-extrabold mb-6">Related Products</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($related as $rel)
                @include('website.partials.product-card', ['product' => $rel])
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
