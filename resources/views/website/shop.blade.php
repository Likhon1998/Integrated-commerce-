@extends('website.layout')
@section('content')

<div class="gaget-shop-header">
    <h1 class="gaget-shop-title">{{ $pageTitle ?? ($activeCategory->name ?? ($activeBrand->name ?? 'Shop All Products')) }}</h1>
    <p class="gaget-shop-sub">{{ $pageSubtitle ?? 'Browse our latest electronics and gadgets.' }}</p>
</div>

<div class="gaget-filter-pills">
    <a href="{{ route('website.shop') }}" class="gaget-pill {{ !isset($activeCategory) && !isset($activeBrand) && !request('filter') ? 'active' : '' }}">All</a>
    <a href="{{ route('website.shop', ['filter'=>'deals']) }}" class="gaget-pill {{ request('filter')==='deals' ? 'active' : '' }}">Deals</a>
    <a href="{{ route('website.shop', ['filter'=>'new']) }}" class="gaget-pill {{ request('filter')==='new' ? 'active' : '' }}">New Arrivals</a>
    <a href="{{ route('website.shop', ['filter'=>'bestsellers']) }}" class="gaget-pill {{ request('filter')==='bestsellers' ? 'active' : '' }}">Best Sellers</a>
    @foreach($categories as $cat)
        <a href="{{ route('website.category', $cat->slug ?? $cat->id) }}" class="gaget-pill {{ (isset($activeCategory) && $activeCategory->id === $cat->id) ? 'active' : '' }}">{{ $cat->name }}</a>
    @endforeach
</div>

<div class="gaget-shop-grid-wrap">
    <div class="gaget-shop-grid">
        @forelse($products as $product)
            @include('website.partials.product-card', ['product' => $product])
        @empty
            <p class="col-span-5 text-center text-gray-500 py-16" style="grid-column:1/-1">No products found.</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
</div>

@endsection
