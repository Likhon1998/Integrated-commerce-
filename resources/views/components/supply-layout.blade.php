@props([
    'title',
    'subtitle' => null,
    'actionUrl' => null,
    'actionLabel' => '+ Add New',
])
<x-app-layout>
    <div class="max-w-7xl mx-auto pt-0 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 mt-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-950 tracking-tight">{{ $title }}</h2>
                @if($subtitle)
                    <p class="mt-1 text-sm text-gray-500 font-medium">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all">
                    {{ $actionLabel }}
                </a>
            @endif
        </div>
        @include('supply.partials.alerts')
        {{ $slot }}
    </div>
</x-app-layout>
