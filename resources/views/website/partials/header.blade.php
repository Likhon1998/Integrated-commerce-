{{-- Top utility bar --}}
<div class="gaget-topbar hidden md:block">
    <div class="max-w-[1280px] mx-auto px-6 py-2 flex items-center justify-between">
        <div class="flex items-center gap-6">
            @forelse($topBarNav ?? [] as $link)
                <span>{{ $link->label }}</span>
            @empty
                <span>Free shipping on all orders over $50</span>
                <span>30-day easy returns</span>
                <span>1 Year Warranty</span>
            @endforelse
        </div>
        <div class="flex items-center gap-5">
            <a href="{{ route('website.track') }}">Track Order</a>
            <a href="{{ route('website.faqs') }}">Help Center</a>
            <a href="{{ route('website.blogs') }}">Blog</a>
            <span class="font-semibold text-slate-700">{{ $settings->currency_code ?? 'USD' }}</span>
        </div>
    </div>
</div>

<div class="gaget-sticky-header">
    {{-- Main header --}}
    <div class="gaget-header-main">
        <div class="max-w-[1280px] mx-auto px-6 py-3.5">
            <div class="flex items-center gap-5">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0 no-underline">
                    @if($settings->logo_path ?? false)
                        <img src="{{ public_storage_url($settings->logo_path) }}" alt="" class="gaget-logo-icon object-contain p-1">
                    @else
                        <div class="gaget-logo-icon">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                    @endif
                    <span class="gaget-logo-text">{{ $settings->store_name ?? 'GAGET STORE' }}</span>
                </a>

                {{-- Search: input | categories | navy button --}}
                <form action="{{ route('website.shop') }}" method="GET" class="hidden lg:flex gaget-search-wrap flex-1 mx-2">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search for products, brands..." class="gaget-search-input">
                    <select name="category" class="gaget-search-select">
                        <option value="">All Categories</option>
                        @foreach($allCategories ?? $categories ?? [] as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category')==$cat->slug?'selected':'' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="gaget-search-btn" aria-label="Search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>

                <div class="flex items-center ml-auto gap-0.5">
                    {{-- Wishlist with hover preview --}}
                    <div class="gaget-action-wrap" x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                        <a href="{{ route('website.wishlist') }}" class="gaget-action-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Wishlist
                            <span x-show="wishlistCount>0" x-text="wishlistCount" class="gaget-cart-badge" x-cloak></span>
                        </a>
                        <div class="gaget-hover-panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                            <div class="gaget-hover-panel__head">
                                <span>Wishlist</span>
                                <strong x-text="wishlistCount + (wishlistCount === 1 ? ' item' : ' items')"></strong>
                            </div>
                            <template x-if="wishlist.length === 0">
                                <p class="gaget-hover-panel__empty">No saved items yet.</p>
                            </template>
                            <div class="gaget-hover-panel__list" x-show="wishlist.length > 0">
                                <template x-for="item in wishlist.slice(0, 4)" :key="'w'+item.id">
                                    <a :href="item.url" class="gaget-hover-panel__row">
                                        <img :src="item.image" :alt="item.name">
                                        <div>
                                            <p x-text="item.name"></p>
                                            <span x-text="currency + Number(item.price).toFixed(2)"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <a href="{{ route('website.wishlist') }}" class="gaget-hover-panel__cta" x-show="wishlistCount > 0">View wishlist</a>
                        </div>
                    </div>

                    {{-- Cart with hover preview --}}
                    <div class="gaget-action-wrap" x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                        <button type="button" @click="cartOpen=true" class="gaget-action-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Cart
                            <span class="gaget-cart-badge" x-text="cartCount" x-show="cartCount > 0" x-cloak></span>
                        </button>
                        <div class="gaget-hover-panel gaget-hover-panel--cart" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                            <div class="gaget-hover-panel__head">
                                <span>Cart</span>
                                <strong x-text="cartCount + (cartCount === 1 ? ' item' : ' items')"></strong>
                            </div>
                            <template x-if="cart.length === 0">
                                <p class="gaget-hover-panel__empty">Your cart is empty.</p>
                            </template>
                            <div class="gaget-hover-panel__list" x-show="cart.length > 0">
                                <template x-for="item in cart.slice(0, 4)" :key="'c'+item.id">
                                    <div class="gaget-hover-panel__row">
                                        <img :src="item.image" :alt="item.name">
                                        <div>
                                            <p x-text="item.name"></p>
                                            <span x-text="item.qty + ' × ' + currency + Number(item.price).toFixed(2)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="gaget-hover-panel__foot" x-show="cart.length > 0">
                                <div class="gaget-hover-panel__total">
                                    <span>Total</span>
                                    <strong x-text="currency + cartTotal.toFixed(2)"></strong>
                                </div>
                                <button type="button" class="gaget-hover-panel__cta" @click="open=false; cartOpen=true">View cart</button>
                            </div>
                        </div>
                    </div>

                    @auth
                        @if(auth()->user()->isStorefrontCustomer())
                            <div class="gaget-action-wrap" x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                                <a href="{{ route('website.account') }}" class="gaget-action-btn" title="{{ auth()->user()->name }}">
                                    @if(auth()->user()->avatarUrl())
                                        <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="h-7 w-7 rounded-full object-cover border border-slate-200">
                                    @else
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700">{{ auth()->user()->avatarInitials() }}</span>
                                    @endif
                                    <span class="hidden xl:inline text-xs font-semibold ml-1 max-w-[72px] truncate">{{ explode(' ', auth()->user()->name)[0] }}</span>
                                </a>
                                <div class="gaget-hover-panel gaget-hover-panel--account" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                                    <div class="gaget-account-menu__user">
                                        @if(auth()->user()->avatarUrl())
                                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="gaget-account-menu__avatar">
                                        @else
                                            <span class="gaget-account-menu__avatar gaget-account-menu__avatar--initials">{{ auth()->user()->avatarInitials() }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="gaget-account-menu__name">{{ auth()->user()->name }}</p>
                                            <p class="gaget-account-menu__email">{{ auth()->user()->email }}</p>
                                        </div>
                                    </div>
                                    <div class="gaget-account-menu__links">
                                        <a href="{{ route('website.account') }}" class="gaget-account-menu__item">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Profile
                                        </a>
                                        <form method="POST" action="{{ route('website.account.logout') }}">
                                            @csrf
                                            <button type="submit" class="gaget-account-menu__item gaget-account-menu__item--danger">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                Sign out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('dashboard') }}" class="gaget-action-btn" title="Staff account">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </a>
                        @endif
                    @else
                        <button type="button" @click="openSignIn()" class="gaget-action-btn" title="Sign in">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="hidden xl:inline text-xs font-semibold ml-1">Sign in</span>
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Full-width navy nav --}}
    <nav class="gaget-navbar hidden md:block">
        <div class="gaget-navbar-inner">
            <div class="gaget-navbar-left">
                <div class="gaget-nav-dropdown">
                    <a href="{{ route('website.shop') }}" class="gaget-all-cat">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        All Categories
                        <svg class="gaget-nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                    <div class="gaget-nav-dropdown-menu gaget-nav-dropdown-menu--wide">
                        @forelse($allCategories ?? [] as $cat)
                            <a href="{{ route('website.category', $cat->slug) }}" class="gaget-nav-dropdown-item">
                                <span>{{ $cat->name }}</span>
                                <span class="gaget-nav-dropdown-count">{{ $cat->products_count ?? 0 }}</span>
                            </a>
                        @empty
                            <span class="gaget-nav-dropdown-empty">No categories yet</span>
                        @endforelse
                    </div>
                </div>
                <div class="gaget-nav-links">
                    @forelse($mainNav ?? [] as $link)
                        <a href="{{ $link->url }}" class="gaget-nav-link">{{ $link->label }}</a>
                    @empty
                        <a href="{{ route('home') }}" class="gaget-nav-link">Home</a>
                        <a href="{{ route('website.shop') }}" class="gaget-nav-link">Shop</a>

                        <div class="gaget-nav-dropdown">
                            <a href="{{ route('website.shop') }}" class="gaget-nav-link gaget-nav-link--dropdown">
                                Categories
                                <svg class="gaget-nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="gaget-nav-dropdown-menu">
                                @forelse($allCategories ?? [] as $cat)
                                    <a href="{{ route('website.category', $cat->slug) }}" class="gaget-nav-dropdown-item">
                                        <span>{{ $cat->name }}</span>
                                        <span class="gaget-nav-dropdown-count">{{ $cat->products_count ?? 0 }}</span>
                                    </a>
                                @empty
                                    <span class="gaget-nav-dropdown-empty">No categories yet</span>
                                @endforelse
                            </div>
                        </div>

                        <a href="{{ route('website.shop', ['filter'=>'deals']) }}" class="gaget-nav-link">Deals</a>
                        <a href="{{ route('website.shop', ['filter'=>'new']) }}" class="gaget-nav-link">New Arrivals</a>
                        <a href="{{ route('website.blogs') }}" class="gaget-nav-link">Blog</a>
                        <a href="{{ route('website.faqs') }}" class="gaget-nav-link">FAQ</a>
                        <a href="{{ route('website.contact') }}" class="gaget-nav-link {{ request()->routeIs('website.contact') ? 'text-blue-300' : '' }}">Contact</a>

                        <div class="gaget-nav-dropdown">
                            <a href="{{ route('home') }}#brands" class="gaget-nav-link gaget-nav-link--dropdown">
                                Brands
                                <svg class="gaget-nav-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="gaget-nav-dropdown-menu">
                                @forelse($brands ?? [] as $brand)
                                    <a href="{{ route('website.brand', \Illuminate\Support\Str::slug($brand->name)) }}" class="gaget-nav-dropdown-item">
                                        <span>{{ $brand->name }}</span>
                                        <span class="gaget-nav-dropdown-count">{{ $brand->products_count ?? 0 }}</span>
                                    </a>
                                @empty
                                    <span class="gaget-nav-dropdown-empty">No brands yet</span>
                                @endforelse
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('website.shop', ['filter'=>'deals']) }}" class="gaget-special-offer">🔥 {{ $settings->special_offer_text ?? 'Special Offer!' }}</a>
        </div>
    </nav>
</div>
