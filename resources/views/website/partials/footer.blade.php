<footer class="bg-gray-900 text-gray-400 mt-16">
    <div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-4 gap-8">
        <div>
            <h4 class="text-white font-bold text-lg mb-4">{{ $settings->store_name ?? 'GAGET STORE' }}</h4>
            <p class="text-sm">{{ $settings->contact_address ?? 'Your trusted electronics destination.' }}</p>
        </div>
        <div>
            <h5 class="text-white font-semibold mb-3">Shop</h5>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('website.shop') }}" class="hover:text-white">All products</a></li>
                <li><a href="{{ route('website.contact') }}" class="hover:text-white">Contact</a></li>
                <li><a href="{{ route('website.wishlist') }}" class="hover:text-white">Wishlist</a></li>
                <li><a href="{{ route('website.compare') }}" class="hover:text-white">Compare</a></li>
                <li><a href="{{ route('website.faqs') }}" class="hover:text-white">FAQ</a></li>
                <li><a href="{{ route('website.track') }}" class="hover:text-white">Track Order</a></li>
            </ul>
        </div>
        <div>
            <h5 class="text-white font-semibold mb-3">Pages</h5>
            <ul class="space-y-2 text-sm">
                @forelse($footerPages ?? [] as $fp)
                    <li><a href="{{ route('website.page', $fp->slug) }}" class="hover:text-white">{{ $fp->title }}</a></li>
                @empty
                    <li class="text-gray-500">About & policy pages appear here when published from CMS.</li>
                @endforelse
            </ul>
        </div>
        <div>
            <h5 class="text-white font-semibold mb-3">Contact</h5>
            <ul class="space-y-2 text-sm">
                @if($settings->contact_email ?? false)<li>{{ $settings->contact_email }}</li>@endif
                @if($settings->contact_phone ?? false)<li>{{ $settings->contact_phone }}</li>@endif
            </ul>
        </div>
    </div>
    <div class="border-t border-gray-800 text-center text-sm py-4">
        &copy; {{ date('Y') }} {{ $settings->store_name ?? 'GAGET STORE' }}. All rights reserved.
    </div>
</footer>
