@extends('website.layout')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">FAQ</h1>
    <p class="mt-2 text-slate-500">Answers to common questions. Managed from admin CMS.</p>

    <div class="mt-8 space-y-3" x-data="{ open: null }">
        @forelse($faqs as $i => $faq)
            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <button type="button" class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left font-semibold text-slate-900" @click="open = open === {{ $i }} ? null : {{ $i }}">
                    <span>{{ $faq->question }}</span>
                    <svg class="w-4 h-4 text-slate-400 shrink-0 transition" :class="open === {{ $i }} && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === {{ $i }}" x-cloak class="px-5 pb-4 text-sm text-slate-600 whitespace-pre-line border-t border-slate-100 pt-3">{{ $faq->answer }}</div>
            </div>
        @empty
            <p class="text-center text-slate-400 py-16">No FAQs published yet.</p>
        @endforelse
    </div>
</div>
@endsection
