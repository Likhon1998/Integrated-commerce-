@extends('website.layout')
@section('content')

@php
    $showSidebar = !empty($showSidebar) && isset($activeCategory);
@endphp

<div class="gaget-shop-page">
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

<div class="gaget-shop-layout {{ $showSidebar ? 'gaget-shop-layout--with-sidebar' : '' }}">
    @if($showSidebar)
        @include('website.partials.category-filters')
    @endif

    <div class="gaget-shop-grid-wrap {{ $showSidebar ? 'gaget-shop-grid-wrap--sidebar' : '' }}">
        <div class="gaget-shop-toolbar">
            <p class="gaget-shop-count">
                <strong>{{ $products->total() }}</strong> item{{ $products->total() === 1 ? '' : 's' }} found
                @isset($activeCategory)
                    in {{ $activeCategory->name }}
                @endisset
            </p>
            @isset($activeCategory)
                <form method="GET" class="gaget-shop-sort">
                    @foreach(request()->except('sort', 'page') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label>
                        Sort by
                        <select name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Latest</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                            <option value="name" @selected(request('sort') === 'name')>Name</option>
                        </select>
                    </label>
                </form>
            @endisset
        </div>

        <div class="gaget-shop-grid {{ $showSidebar ? 'gaget-shop-grid--sidebar' : '' }}">
            @forelse($products as $product)
                @include('website.partials.product-card', ['product' => $product])
            @empty
                <p class="col-span-5 text-center text-gray-500 py-16" style="grid-column:1/-1">No products found.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    </div>
</div>
</div>

@endsection
