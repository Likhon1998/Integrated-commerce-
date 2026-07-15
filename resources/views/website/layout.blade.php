<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings->store_name ?? 'GAGET STORE')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/website.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="gaget-store bg-white antialiased" x-data="storefrontCart()" @keydown.escape.window="cartOpen=false; checkoutOpen=false">

@include('website.partials.header')

<main>
    @yield('content')
</main>

@include('website.partials.footer')

{{-- Cart drawer --}}
<div x-show="cartOpen" x-cloak class="fixed inset-0 z-50">
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
            <button @click="checkoutOpen=true; cartOpen=false" class="w-full gaget-btn-primary text-center">Checkout</button>
        </div>
    </div>
</div>

<div x-show="checkoutOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" @click="checkoutOpen=false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
        <h3 class="text-xl font-bold">Place Order</h3>
        <input x-model="checkout.name" type="text" placeholder="Full Name" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm">
        <input x-model="checkout.phone" type="text" placeholder="Phone Number" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm">
        <textarea x-model="checkout.address" placeholder="Delivery Address" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm" rows="2"></textarea>
        <button @click="placeOrder()" :disabled="ordering" class="w-full gaget-btn-primary text-center disabled:opacity-50">
            <span x-text="ordering?'Placing...':'Confirm Order (Cash on Delivery)'"></span>
        </button>
        <p x-show="orderMessage" x-text="orderMessage" class="text-sm text-center" :class="orderSuccess?'text-green-600':'text-red-600'"></p>
    </div>
</div>

<script>
function storefrontCart() {
    return {
        cart: JSON.parse(localStorage.getItem('gaget_cart')||'[]'),
        wishlist: JSON.parse(localStorage.getItem('gaget_wishlist')||'[]'),
        compare: JSON.parse(localStorage.getItem('gaget_compare')||'[]'),
        cartOpen: false, checkoutOpen: false, ordering: false,
        orderMessage: '', orderSuccess: false,
        currency: @json($settings->currency_symbol ?? '$'),
        checkout: { name:'', phone:'', address:'' },
        get cartCount() { return this.cart.reduce((s,i)=>s+i.qty,0); },
        get cartTotal() { return this.cart.reduce((s,i)=>s+i.price*i.qty,0); },
        get wishlistCount() { return this.wishlist.length; },
        get compareCount() { return this.compare.length; },
        save() { localStorage.setItem('gaget_cart', JSON.stringify(this.cart)); },
        saveWishlist() { localStorage.setItem('gaget_wishlist', JSON.stringify(this.wishlist)); },
        saveCompare() { localStorage.setItem('gaget_compare', JSON.stringify(this.compare)); },
        addToCart(product) {
            const ex = this.cart.find(i=>i.id===product.id);
            if(ex) ex.qty++; else this.cart.push({...product, qty:1});
            this.save();
        },
        updateQty(i,d) { this.cart[i].qty+=d; if(this.cart[i].qty<=0)this.cart.splice(i,1); this.save(); },
        removeItem(i) { this.cart.splice(i,1); this.save(); },
        inWishlist(id) { return this.wishlist.some(i=>i.id===id); },
        inCompare(id) { return this.compare.some(i=>i.id===id); },
        toggleWishlist(product) {
            const idx = this.wishlist.findIndex(i=>i.id===product.id);
            if (idx >= 0) this.wishlist.splice(idx, 1);
            else this.wishlist.push(product);
            this.saveWishlist();
        },
        toggleCompare(product) {
            const idx = this.compare.findIndex(i=>i.id===product.id);
            if (idx >= 0) {
                this.compare.splice(idx, 1);
            } else {
                if (this.compare.length >= 4) {
                    alert('You can compare up to 4 products.');
                    return;
                }
                this.compare.push(product);
            }
            this.saveCompare();
        },
        async placeOrder() {
            if(!this.checkout.name||!this.checkout.phone){ this.orderMessage='Name and phone required.'; this.orderSuccess=false; return; }
            this.ordering=true; this.orderMessage='';
            try {
                const res = await fetch(@json(route('website.checkout')), {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
                    body: JSON.stringify({cart:this.cart, customer_name:this.checkout.name, customer_phone:this.checkout.phone, customer_address:this.checkout.address})
                });
                const data = await res.json();
                if(data.success){ this.cart=[]; this.save(); this.orderSuccess=true; this.orderMessage='Order placed! Invoice: '+data.invoice; this.checkout={name:'',phone:'',address:''}; }
                else { this.orderSuccess=false; this.orderMessage=data.message||'Order failed.'; }
            } catch(e){ this.orderSuccess=false; this.orderMessage='Network error.'; }
            this.ordering=false;
        }
    }
}
</script>
@stack('scripts')
</body>
</html>
