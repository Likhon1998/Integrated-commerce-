<!DOCTYPE html>
<html lang="en" class="h-full overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Terminal — {{ config('app.name', 'Nexa POS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { height: 100%; width: 100%; margin: 0; overflow: hidden; }
    </style>
</head>
<body class="h-full overflow-hidden font-sans antialiased text-gray-900 bg-slate-100">
    {{ $slot }}
    <script>
        setInterval(function () {
            fetch('/refresh-session', { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(function () {});
        }, 15 * 60 * 1000);
    </script>
</body>
</html>