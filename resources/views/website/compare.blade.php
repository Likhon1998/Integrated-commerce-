@extends('website.layout')
@section('title', 'Compare — '.($settings->store_name ?? 'GAGET STORE'))
@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-8">
        <div>
            <p class="text-xs font-bold tracking-[0.15em] uppercase text-blue-600">Side by side</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900">Compare products</h1>
            <p class="mt-1 text-sm text-slate-500">Up to 4 products · <span x-text="compareCount"></span> selected</p>
        </div>
        <button type="button" x-show="compareCount>0" @click="compare=[]; saveCompare()" class="text-xs font-semibold text-rose-600 hover:underline">Clear compare</button>
    </div>

    <template x-if="compare.length===0">
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 py-20 text-center">
            <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <p class="mt-4 text-slate-600 font-semibold">No products to compare</p>
            <p class="mt-1 text-sm text-slate-400">Use the compare icon on products to add them here.</p>
            <a href="{{ route('website.shop') }}" class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Browse products</a>
        </div>
    </template>

    <div x-show="compare.length>0" class="overflow-x-auto rounded-2xl border border-slate-100 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-4 py-4 text-left text-xs font-bold uppercase text-slate-400 w-36 sticky left-0 bg-white">Feature</th>
                    <template x-for="item in compare" :key="'h'+item.id">
                        <th class="px-4 py-4 min-w-[180px] align-bottom">
                            <div class="relative">
                                <button type="button" class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600 text-xs" @click="toggleCompare(item)">×</button>
                                <a :href="item.url" class="block">
                                    <img :src="item.image" class="h-28 w-full object-contain bg-slate-50 rounded-xl mb-3" alt="">
                                    <span class="font-semibold text-slate-900 line-clamp-2" x-text="item.name"></span>
                                </a>
                            </div>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <tr>
                    <td class="px-4 py-3 text-xs font-bold uppercase text-slate-400 sticky left-0 bg-white">Price</td>
                    <template x-for="item in compare" :key="'p'+item.id">
                        <td class="px-4 py-3 font-bold text-blue-600" x-text="currency+Number(item.price).toFixed(2)"></td>
                    </template>
                </tr>
                <tr class="bg-slate-50/50">
                    <td class="px-4 py-3 text-xs font-bold uppercase text-slate-400 sticky left-0 bg-slate-50">Category</td>
                    <template x-for="item in compare" :key="'c'+item.id">
                        <td class="px-4 py-3 text-slate-600" x-text="item.category || '—'"></td>
                    </template>
                </tr>
                <tr>
                    <td class="px-4 py-3 text-xs font-bold uppercase text-slate-400 sticky left-0 bg-white">Rating</td>
                    <template x-for="item in compare" :key="'r'+item.id">
                        <td class="px-4 py-3 text-amber-500" x-text="(item.rating ? '★ '+Number(item.rating).toFixed(1) : '—')"></td>
                    </template>
                </tr>
                <tr class="bg-slate-50/50">
                    <td class="px-4 py-3 text-xs font-bold uppercase text-slate-400 sticky left-0 bg-slate-50">Actions</td>
                    <template x-for="item in compare" :key="'a'+item.id">
                        <td class="px-4 py-3">
                            <button type="button" class="rounded-lg bg-slate-900 text-white text-xs font-bold px-3 py-2 hover:bg-blue-600"
                                    @click="addToCart({id:item.id,name:item.name,price:item.price,image:item.image}); cartOpen=true">Add to cart</button>
                        </td>
                    </template>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
