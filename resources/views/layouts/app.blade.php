<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Nexa POS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-panel font-sans antialiased text-slate-900 bg-[#F4F6FB]">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        @include('layouts.navigation')

        <div class="admin-scroll-hide relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            @isset($header)
                <header class="bg-white/80 backdrop-blur border-b border-slate-100 mt-16 lg:mt-[4.25rem]">
                    <div class="max-w-[1400px] mx-auto py-3 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="w-full grow p-4 sm:p-6 {{ !isset($header) ? 'mt-16 lg:mt-[4.25rem]' : '' }}">
                <div class="max-w-[1400px] mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script>
        setInterval(function() {
            fetch('/refresh-session', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function () {});
        }, 15 * 60 * 1000);
    </script>
</body>
</html>
