<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings->store_name ?? 'GAGET STORE')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @php
        $storefrontUser = auth()->check() && auth()->user()->isStorefrontCustomer()
            ? [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => auth()->user()->customerProfile?->phone ?? '',
                'address' => auth()->user()->customerProfile?->address ?? '',
            ]
            : null;
    @endphp
    <script>
        function readStorefrontList(key) {
            try {
                const raw = localStorage.getItem(key);
                const parsed = raw ? JSON.parse(raw) : [];
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function headerSearch(suggestUrl, shopBaseUrl, currencySymbol) {
            return {
                q: @json(request('search', '')),
                category: @json(request('category', '')),
                open: false,
                loading: false,
                products: [],
                mode: 'best',
                activeIndex: -1,
                currency: currencySymbol || '$',
                _req: 0,
                get shopUrl() {
                    const params = new URLSearchParams();
                    if (this.q) params.set('search', this.q);
                    if (this.category) params.set('category', this.category);
                    const qs = params.toString();
                    return qs ? `${shopBaseUrl}?${qs}` : shopBaseUrl;
                },
                onFocus() {
                    this.open = true;
                    this.fetchSuggestions();
                },
                async fetchSuggestions() {
                    this.open = true;
                    this.loading = true;
                    this.activeIndex = -1;
                    const req = ++this._req;
                    try {
                        const params = new URLSearchParams({
                            q: this.q || '',
                            category: this.category || '',
                            limit: '8',
                        });
                        const res = await fetch(`${suggestUrl}?${params.toString()}`, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await res.json();
                        if (req !== this._req) return;
                        this.products = Array.isArray(data.products) ? data.products : [];
                        this.mode = data.mode || (this.q ? 'search' : 'best');
                    } catch (e) {
                        if (req !== this._req) return;
                        this.products = [];
                    } finally {
                        if (req === this._req) this.loading = false;
                    }
                },
                move(step) {
                    if (!this.products.length) return;
                    this.open = true;
                    const len = this.products.length;
                    this.activeIndex = (this.activeIndex + step + len) % len;
                },
                onEnter(event) {
                    if (this.open && this.activeIndex >= 0 && this.products[this.activeIndex]) {
                        event.preventDefault();
                        window.location.href = this.products[this.activeIndex].url;
                    }
                },
            };
        }
        window.headerSearch = headerSearch;

        function storefrontCart() {
            return {
                cart: readStorefrontList('gaget_cart'),
                wishlist: readStorefrontList('gaget_wishlist'),
                cartOpen: false,
                checkoutOpen: false,
                checkoutStep: 'auth',
                authPurpose: 'account',
                authTab: 'login',
                authLoading: false,
                authMessage: '',
                ordering: false,
                orderMessage: '',
                lastOrderId: null,
                lastOrderInvoice: '',
                orderSuccess: false,
                toastMessage: '',
                toastVisible: false,
                isLoggedIn: @json((bool) $storefrontUser),
                currency: @json($settings->currency_symbol ?? '$'),
                checkout: {
                    name: @json(data_get($storefrontUser, 'name', '')),
                    phone: @json(data_get($storefrontUser, 'phone', '')),
                    address: @json(data_get($storefrontUser, 'address', '')),
                },
                authLogin: { email: '', password: '' },
                authRegister: { name: '', phone: '', email: '', password: '', address: '' },
                get cartCount() { return this.cart.reduce((s, i) => s + (Number(i.qty) || 0), 0); },
                get cartTotal() { return this.cart.reduce((s, i) => s + (Number(i.price) || 0) * (Number(i.qty) || 0), 0); },
                get wishlistCount() { return this.wishlist.length; },
                save() { localStorage.setItem('gaget_cart', JSON.stringify(this.cart)); },
                saveWishlist() { localStorage.setItem('gaget_wishlist', JSON.stringify(this.wishlist)); },
                addToCart(product, qty = 1, openDrawer = true) {
                    if (!product || product.id === undefined || product.id === null) return;

                    const id = Number(product.id);
                    const amount = Math.max(1, Number(qty) || 1);
                    const next = this.cart.map((item) => ({ ...item }));
                    const existing = next.find((item) => Number(item.id) === id);

                    if (existing) {
                        existing.qty = (Number(existing.qty) || 0) + amount;
                    } else {
                        next.push({
                            id,
                            name: product.name || 'Product',
                            price: Number(product.price) || 0,
                            image: product.image || '',
                            qty: amount,
                        });
                    }

                    this.cart = next;
                    this.save();
                    if (openDrawer) this.cartOpen = true;
                },
                updateQty(i, d) {
                    const next = this.cart.map((item) => ({ ...item }));
                    next[i].qty = (Number(next[i].qty) || 0) + d;
                    this.cart = next[i].qty <= 0 ? next.filter((_, idx) => idx !== i) : next;
                    this.save();
                },
                removeItem(i) {
                    this.cart = this.cart.filter((_, idx) => idx !== i);
                    this.save();
                },
                inWishlist(id) { return this.wishlist.some((i) => Number(i.id) === Number(id)); },
                toggleWishlist(product) {
                    const idx = this.wishlist.findIndex((i) => Number(i.id) === Number(product.id));
                    if (idx >= 0) this.wishlist.splice(idx, 1);
                    else this.wishlist.push(product);
                    this.saveWishlist();
                },
                flashToast(message) {
                    this.toastMessage = message;
                    this.toastVisible = true;
                    clearTimeout(this._toastTimer);
                    this._toastTimer = setTimeout(() => { this.toastVisible = false; }, 2200);
                },
                prefillCheckout(profile) {
                    this.checkout.name = profile?.name || '';
                    this.checkout.phone = profile?.phone || '';
                    this.checkout.address = profile?.address || '';
                },
                startCheckout() {
                    if (this.cart.length === 0) return;
                    this.cartOpen = false;
                    this.checkoutOpen = true;
                    this.authPurpose = 'checkout';
                    this.orderMessage = '';
                    this.orderSuccess = false;
                    this.authMessage = '';
                    if (this.isLoggedIn) {
                        this.checkoutStep = 'order';
                    } else {
                        this.checkoutStep = 'auth';
                        this.authTab = 'login';
                    }
                },
                openSignIn(tab = 'login') {
                    this.cartOpen = false;
                    this.checkoutOpen = true;
                    this.authPurpose = 'account';
                    this.checkoutStep = 'auth';
                    this.authTab = tab === 'register' ? 'register' : 'login';
                    this.authMessage = '';
                    this.orderMessage = '';
                    this.orderSuccess = false;
                },
                afterAuthSuccess(user) {
                    this.isLoggedIn = true;
                    this.prefillCheckout(user);
                    if (this.authPurpose === 'checkout') {
                        this.checkoutStep = 'order';
                        return;
                    }
                    window.location.href = @json(route('website.account'));
                },
                async submitLogin() {
                    this.authLoading = true;
                    this.authMessage = '';
                    try {
                        const res = await fetch(@json(route('website.account.login')), {
                            method: 'POST',
                            headers: this.jsonHeaders(),
                            body: JSON.stringify(this.authLogin),
                        });
                        const data = await res.json();
                        if (!res.ok) {
                            this.authMessage = data.message || data.errors?.email?.[0] || 'Sign in failed.';
                            return;
                        }
                        this.afterAuthSuccess(data.user);
                    } catch (e) {
                        this.authMessage = 'Network error. Please try again.';
                    } finally {
                        this.authLoading = false;
                    }
                },
                async submitRegister() {
                    if (!this.authRegister.name || !this.authRegister.phone || !this.authRegister.email || !this.authRegister.password) {
                        this.authMessage = 'Please fill name, phone, email, and password.';
                        return;
                    }
                    this.authLoading = true;
                    this.authMessage = '';
                    try {
                        const res = await fetch(@json(route('website.account.register')), {
                            method: 'POST',
                            headers: this.jsonHeaders(),
                            body: JSON.stringify(this.authRegister),
                        });
                        const data = await res.json();
                        if (!res.ok) {
                            const err = data.errors || {};
                            this.authMessage = err.name?.[0] || err.email?.[0] || err.phone?.[0] || err.password?.[0] || data.message || 'Registration failed.';
                            return;
                        }
                        this.afterAuthSuccess(data.user);
                    } catch (e) {
                        this.authMessage = 'Network error. Please try again.';
                    } finally {
                        this.authLoading = false;
                    }
                },
                async placeOrder() {
                    if (!this.checkout.name || !this.checkout.phone || !this.checkout.address) {
                        this.orderMessage = 'Name, phone, and delivery address are required.';
                        this.orderSuccess = false;
                        return;
                    }
                    if (!this.isLoggedIn) {
                        this.checkoutStep = 'auth';
                        this.authTab = 'login';
                        return;
                    }
                    this.ordering = true;
                    this.orderMessage = '';
                    try {
                        const res = await fetch(@json(route('website.checkout')), {
                            method: 'POST',
                            headers: this.jsonHeaders(),
                            body: JSON.stringify({
                                cart: this.cart,
                                customer_name: this.checkout.name,
                                customer_phone: this.checkout.phone,
                                customer_address: this.checkout.address,
                            }),
                        });
                        const data = await res.json();
                        if (res.status === 401 || data.auth_required) {
                            this.isLoggedIn = false;
                            this.checkoutStep = 'auth';
                            this.authTab = 'login';
                            this.authMessage = 'Please sign in to place your order.';
                            return;
                        }
                        if (data.success) {
                            if (!data.invoice && !data.order_id) {
                                this.orderSuccess = false;
                                this.orderMessage = 'Order placed but no Order ID was returned. Please check My Orders or contact support.';
                                return;
                            }
                            this.cart = [];
                            this.save();
                            this.orderSuccess = true;
                            this.lastOrderId = data.order_id || null;
                            this.lastOrderInvoice = data.invoice || '';
                            this.orderMessage = this.lastOrderInvoice
                                ? ('Order ID: ' + this.lastOrderInvoice)
                                : ('Order #' + this.lastOrderId);
                            this.checkoutStep = 'success';
                        } else {
                            this.orderSuccess = false;
                            this.orderMessage = data.message || 'Order failed.';
                        }
                    } catch (e) {
                        this.orderSuccess = false;
                        this.orderMessage = 'Network error.';
                    }
                    this.ordering = false;
                },
                jsonHeaders() {
                    return {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    };
                },
            };
        }
        window.storefrontCart = storefrontCart;
    </script>
    @vite(['resources/css/app.css', 'resources/css/website.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="gaget-store bg-white antialiased" id="storefront-root" x-data="storefrontCart()" @keydown.escape.window="cartOpen=false; checkoutOpen=false">

@include('website.partials.header')

<main>
    @yield('content')
</main>

@include('website.partials.footer')

{{-- Cart drawer --}}
<div x-show="cartOpen" x-cloak class="fixed inset-0 z-[70]">
    <div class="absolute inset-0 bg-black/50" @click="cartOpen=false"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 class="text-lg font-bold">Your Cart (<span x-text="cartCount"></span>)</h3>
            <button @click="cartOpen=false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <template x-if="cart.length===0"><p class="text-gray-500 text-center py-10">Your cart is empty.</p></template>
            <template x-for="(item,i) in cart" :key="item.id">
                <div class="flex gap-3 border rounded-xl p-3">
                    <img :src="item.image" class="w-16 h-16 object-contain rounded-lg bg-gray-50" alt="">
                    <div class="flex-1">
                        <p class="font-semibold text-sm" x-text="item.name"></p>
                        <p class="text-blue-600 font-bold text-sm" x-text="currency+item.price.toFixed(2)"></p>
                        <div class="flex items-center gap-2 mt-2">
                            <button @click="updateQty(i,-1)" class="w-7 h-7 rounded bg-gray-100 font-bold">−</button>
                            <span x-text="item.qty" class="text-sm font-medium w-6 text-center"></span>
                            <button @click="updateQty(i,1)" class="w-7 h-7 rounded bg-gray-100 font-bold">+</button>
                            <button @click="removeItem(i)" class="ml-auto text-red-500 text-xs font-semibold">Remove</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div class="border-t p-5" x-show="cart.length>0">
            <div class="flex justify-between font-bold text-lg mb-3"><span>Total</span><span x-text="currency+cartTotal.toFixed(2)"></span></div>
            <button @click="startCheckout()" class="w-full gaget-btn-primary text-center">Checkout</button>
        </div>
    </div>
</div>

{{-- Checkout: sign in / register / place order (one modal) --}}
<div x-show="checkoutOpen" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" @click="checkoutOpen=false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6">
        <button type="button" @click="checkoutOpen=false" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>

        {{-- Auth step (standalone account OR checkout) --}}
        <div x-show="checkoutStep==='auth'" x-cloak>
            <h3 class="text-xl font-bold text-slate-900 pr-8" x-text="authPurpose === 'checkout' ? 'Sign in to checkout' : (authTab === 'register' ? 'Create your account' : 'Welcome back')"></h3>
            <p class="text-sm text-slate-500 mt-1 mb-5" x-text="authPurpose === 'checkout' ? 'Sign in or create an account to place your order.' : 'Create an account anytime — no order required.'"></p>

            <div class="flex rounded-xl bg-slate-100 p-1 mb-5">
                <button type="button" @click="authTab='login'; authMessage=''" class="flex-1 rounded-lg py-2 text-sm font-semibold transition" :class="authTab==='login' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'">Sign in</button>
                <button type="button" @click="authTab='register'; authMessage=''" class="flex-1 rounded-lg py-2 text-sm font-semibold transition" :class="authTab==='register' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'">Create account</button>
            </div>

            <div x-show="authTab==='login'" class="space-y-3">
                <input x-model="authLogin.email" type="email" placeholder="Email" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" autocomplete="email">
                <input x-model="authLogin.password" type="password" placeholder="Password" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" autocomplete="current-password" @keydown.enter.prevent="submitLogin()">
                <button type="button" @click="submitLogin()" :disabled="authLoading" class="w-full gaget-btn-primary text-center disabled:opacity-50">
                    <span x-text="authLoading ? 'Signing in...' : (authPurpose === 'checkout' ? 'Sign in & continue' : 'Sign in')"></span>
                </button>
            </div>

            <div x-show="authTab==='register'" class="space-y-3">
                <input x-model="authRegister.name" type="text" placeholder="Full name" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" autocomplete="name">
                <input x-model="authRegister.phone" type="text" placeholder="Phone number" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" autocomplete="tel">
                <input x-model="authRegister.email" type="email" placeholder="Email" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" autocomplete="email">
                <input x-model="authRegister.password" type="password" placeholder="Password (min. 8 characters)" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" autocomplete="new-password">
                <textarea x-model="authRegister.address" placeholder="Address (optional)" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" rows="2"></textarea>
                <button type="button" @click="submitRegister()" :disabled="authLoading" class="w-full gaget-btn-primary text-center disabled:opacity-50">
                    <span x-text="authLoading ? 'Creating account...' : (authPurpose === 'checkout' ? 'Create account & continue' : 'Create account')"></span>
                </button>
            </div>

            <p x-show="authMessage" x-text="authMessage" class="mt-3 text-sm text-center text-rose-600"></p>
        </div>

        {{-- Order step --}}
        <div x-show="checkoutStep==='order'" x-cloak>
            <h3 class="text-xl font-bold text-slate-900 pr-8">Place order</h3>
            <p class="text-sm text-slate-500 mt-1 mb-5">Cash on delivery · <span x-text="cartCount"></span> item(s) · <span class="font-semibold text-slate-800" x-text="currency+cartTotal.toFixed(2)"></span></p>

            <div class="space-y-3">
                <input x-model="checkout.name" type="text" placeholder="Full name" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm">
                <input x-model="checkout.phone" type="text" placeholder="Phone number" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm">
                <textarea x-model="checkout.address" placeholder="Delivery address" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" rows="2"></textarea>
                <button type="button" @click="placeOrder()" :disabled="ordering" class="w-full gaget-btn-primary text-center disabled:opacity-50">
                    <span x-text="ordering ? 'Placing order...' : 'Confirm order (Cash on delivery)'"></span>
                </button>
            </div>

            <p x-show="orderMessage" x-text="orderMessage" class="mt-3 text-sm text-center" :class="orderSuccess ? 'text-green-600' : 'text-red-600'"></p>
        </div>

        {{-- Success step --}}
        <div x-show="checkoutStep==='success'" x-cloak class="text-center py-4">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-2xl">✓</div>
            <h3 class="text-xl font-bold text-slate-900">Order placed!</h3>
            <p class="text-sm text-slate-500 mt-2">Save this Order ID for tracking and support.</p>
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">Order ID</p>
                <p class="mt-1 font-mono text-lg font-extrabold text-emerald-900" x-text="lastOrderInvoice || ('#' + lastOrderId)"></p>
                <p class="mt-1 text-[11px] text-emerald-700/80" x-show="lastOrderId" x-text="'Ref #' + lastOrderId"></p>
            </div>
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('website.account') }}" class="gaget-btn-primary block w-full text-center text-sm py-3">View my orders</a>
                <button type="button" @click="checkoutOpen=false" class="text-sm font-semibold text-slate-600 hover:text-slate-800">Continue shopping</button>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div x-show="toastVisible" x-cloak x-transition.opacity.duration.200ms
     class="fixed bottom-6 left-1/2 z-[90] -translate-x-1/2 rounded-xl bg-slate-900 px-4 py-2.5 text-[13px] font-semibold text-white shadow-lg">
    <span x-text="toastMessage"></span>
</div>

<script>
window.getStorefrontRoot = function () {
    const root = document.getElementById('storefront-root') || document.body;
    if (!window.Alpine || typeof Alpine.$data !== 'function') return null;
    try {
        return Alpine.$data(root);
    } catch (e) {
        return null;
    }
};

window.addProductToCart = function (product, qty = 1, openDrawer = true) {
    const root = window.getStorefrontRoot();
    if (root && typeof root.addToCart === 'function') {
        root.addToCart(product, qty, openDrawer);
        return true;
    }
    return false;
};

document.addEventListener('click', function (event) {
    const btn = event.target.closest('[data-add-to-cart]');
    if (!btn) return;

    event.preventDefault();
    event.stopPropagation();

    let product;
    try {
        product = JSON.parse(btn.getAttribute('data-add-to-cart'));
    } catch (e) {
        return;
    }

    const qtyAttr = btn.getAttribute('data-qty');
    const qty = Math.max(1, Number(qtyAttr || 1) || 1);
    const openDrawer = btn.getAttribute('data-open-cart') !== '0';
    const goCheckout = btn.getAttribute('data-checkout') === '1';

    const finish = function () {
        if (goCheckout) {
            const root = window.getStorefrontRoot();
            if (root && typeof root.startCheckout === 'function') {
                root.startCheckout();
            }
        }
    };

    if (window.addProductToCart(product, qty, goCheckout ? false : openDrawer)) {
        finish();
        return;
    }

    // Alpine not ready yet — wait and add once so the badge shows "1" immediately after boot.
    let tries = 0;
    const timer = setInterval(function () {
        tries += 1;
        if (window.addProductToCart(product, qty, goCheckout ? false : openDrawer)) {
            clearInterval(timer);
            finish();
        } else if (tries > 50) {
            clearInterval(timer);
        }
    }, 40);
});
</script>
@stack('scripts')
</body>
</html>
