@extends('website.layout')

@section('content')
<article class="max-w-3xl mx-auto px-4 py-12">
    <p class="text-sm text-slate-400 mb-2"><a href="{{ route('website.blogs') }}" class="hover:text-slate-700">Blog</a></p>
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $blog->title }}</h1>
    <div class="mt-2 text-sm text-slate-400">
        {{ optional($blog->published_at)->format('M d, Y') }}
        @if($blog->author_name) · {{ $blog->author_name }} @endif
    </div>
    @if($blog->cover_image)
        <img src="{{ public_storage_url($blog->cover_image) }}" alt="" class="mt-6 w-full rounded-2xl object-cover max-h-96">
    @endif
    @if($blog->excerpt)
        <p class="mt-6 text-lg text-slate-500">{{ $blog->excerpt }}</p>
    @endif
    <div class="mt-8 whitespace-pre-line text-slate-700 leading-relaxed">{{ $blog->body }}</div>
</article>
@endsection
