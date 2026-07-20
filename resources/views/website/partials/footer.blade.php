@php
    $social = collect($settings->social_links ?? [])->filter();
    $newsletterTitle = $settings->contact_newsletter_title ?? $settings->blog_newsletter_title ?? 'Stay Updated';
    $newsletterText = $settings->contact_newsletter_text ?? $settings->blog_newsletter_text ?? 'Subscribe to get special offers, new arrivals, and updates.';
@endphp
<footer class="tn-footer">
    <div class="tn-footer-newsletter">
        <div class="tn-container tn-footer-newsletter-inner">
            <div>
                <h3 class="tn-footer-newsletter-title">{{ $newsletterTitle }}</h3>
                <p class="tn-footer-newsletter-text">{{ $newsletterText }}</p>
            </div>
            <form action="{{ route('website.contact') }}" method="GET" class="tn-footer-newsletter-form">
                <input type="email" name="email" placeholder="Enter your email" class="tn-footer-newsletter-input" required>
                <button type="submit" class="tn-btn tn-btn-primary tn-btn-sm">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="tn-container tn-footer-main">
        <div class="tn-footer-brand">
            <a href="{{ route('home') }}" class="tn-footer-logo">{{ $settings->store_name ?? 'Store' }}</a>
            <p class="tn-footer-about">{{ $settings->contact_address ?? 'Your trusted destination for quality products and great service.' }}</p>
            @if($social->isNotEmpty())
                <div class="tn-footer-social">
                    @foreach($social as $link)
                        @if(!empty($link['url']))
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener" aria-label="{{ $link['label'] ?? 'Social' }}">{{ $link['label'] ?? 'Link' }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h4 class="tn-footer-heading">Shop</h4>
            <ul class="tn-footer-links">
                <li><a href="{{ route('website.shop') }}">All Products</a></li>
                <li><a href="{{ route('website.shop', ['filter' => 'deals']) }}">Deals</a></li>
                <li><a href="{{ route('website.shop', ['filter' => 'new']) }}">New Arrivals</a></li>
                <li><a href="{{ route('website.shop', ['filter' => 'bestsellers']) }}">Best Sellers</a></li>
            </ul>
        </div>

        <div>
            <h4 class="tn-footer-heading">Customer Service</h4>
            <ul class="tn-footer-links">
                <li><a href="{{ route('website.contact') }}">Contact Us</a></li>
                <li><a href="{{ route('website.faqs') }}">FAQ</a></li>
                <li><a href="{{ route('website.wishlist') }}">Wishlist</a></li>
                @auth
                    @if(auth()->user()->isStorefrontCustomer())
                        <li><a href="{{ route('website.account') }}">My Account</a></li>
                    @endif
                @endauth
            </ul>
        </div>

        <div>
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
        <div class="tn-container">
            <p>&copy; {{ date('Y') }} {{ $settings->store_name ?? 'Store' }}. All rights reserved.</p>
        </div>
    </div>
</footer>
