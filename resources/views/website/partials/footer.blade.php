@php
    $social = collect($settings->social_links ?? [])->filter();
@endphp
<footer class="tn-footer">
    {{-- Technical motion layer --}}
    <div class="tn-footer-fx" aria-hidden="true">
        <div class="tn-footer-fx__grid"></div>
        <div class="tn-footer-fx__scan"></div>
        <svg class="tn-footer-fx__wave tn-footer-fx__wave--1" viewBox="0 0 1440 60" preserveAspectRatio="none">
            <path d="M0,30 C120,10 240,50 360,30 C480,10 600,50 720,30 C840,10 960,50 1080,30 C1200,10 1320,50 1440,30" fill="none" stroke="currentColor" stroke-width="1.2"/>
        </svg>
        <svg class="tn-footer-fx__wave tn-footer-fx__wave--2" viewBox="0 0 1440 60" preserveAspectRatio="none">
            <path d="M0,32 C160,52 320,12 480,32 C640,52 800,12 960,32 C1120,52 1280,12 1440,32" fill="none" stroke="currentColor" stroke-width="1"/>
        </svg>
        <span class="tn-footer-fx__node tn-footer-fx__node--1"></span>
        <span class="tn-footer-fx__node tn-footer-fx__node--2"></span>
        <span class="tn-footer-fx__node tn-footer-fx__node--3"></span>
        <span class="tn-footer-fx__node tn-footer-fx__node--4"></span>
        <span class="tn-footer-fx__node tn-footer-fx__node--5"></span>
        <span class="tn-footer-fx__beam tn-footer-fx__beam--1"></span>
        <span class="tn-footer-fx__beam tn-footer-fx__beam--2"></span>
    </div>

    <div class="tn-container tn-footer-main">
        <div class="tn-footer-brand">
            <a href="{{ route('home') }}" class="tn-footer-logo">
                <span class="tn-footer-logo__pulse" aria-hidden="true"></span>
                {{ $settings->store_name ?? 'Store' }}
            </a>
            @if($social->isNotEmpty())
                <div class="tn-footer-social">
                    @foreach($social as $link)
                        @if(!empty($link['url']))
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener" class="tn-footer-social-chip" aria-label="{{ $link['label'] ?? 'Social' }}">{{ $link['label'] ?? 'Link' }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div class="tn-footer-col">
            <h4 class="tn-footer-heading">Shop</h4>
            <ul class="tn-footer-links">
                <li><a href="{{ route('website.shop') }}">All Products</a></li>
                <li><a href="{{ route('website.shop', ['filter' => 'deals']) }}">Deals</a></li>
                <li><a href="{{ route('website.shop', ['filter' => 'new']) }}">New</a></li>
                <li><a href="{{ route('website.shop', ['filter' => 'bestsellers']) }}">Best Sellers</a></li>
            </ul>
        </div>

        <div class="tn-footer-col">
            <h4 class="tn-footer-heading">Help</h4>
            <ul class="tn-footer-links">
                <li><a href="{{ route('website.contact') }}">Contact</a></li>
                <li><a href="{{ route('website.faqs') }}">FAQ</a></li>
                <li><a href="{{ route('website.wishlist') }}">Wishlist</a></li>
                @auth
                    @if(auth()->user()->isStorefrontCustomer())
                        <li><a href="{{ route('website.account') }}">Account</a></li>
                    @endif
                @endauth
            </ul>
        </div>

        <div class="tn-footer-col">
            <h4 class="tn-footer-heading">Company</h4>
            <ul class="tn-footer-links">
                @forelse($footerPages ?? [] as $fp)
                    <li><a href="{{ route('website.page', $fp->slug) }}">{{ $fp->title }}</a></li>
                @empty
                    <li><a href="{{ route('website.blogs') }}">Blog</a></li>
                @endforelse
                @if($settings->contact_email ?? false)
                    <li><a href="mailto:{{ $settings->contact_email }}">{{ $settings->contact_email }}</a></li>
                @endif
                @if($settings->contact_phone ?? false)
                    <li>{{ $settings->contact_phone }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="tn-footer-bottom">
        <div class="tn-container tn-footer-bottom-inner">
            <p>&copy; {{ date('Y') }} {{ $settings->store_name ?? 'Store' }}. All rights reserved.</p>
            <span class="tn-footer-signal" aria-hidden="true">
                <i></i><i></i><i></i>
            </span>
        </div>
    </div>
</footer>
