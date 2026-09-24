<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Shyra Beautique') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|playfair-display:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="sb-auth-shell">
            <a class="sb-brand sb-auth-brand" href="/"><span class="sb-brand-mark">S</span><span>Shyra <i>Beautique</i><small>GOWN RESERVATION & RENTAL</small></span></a>
            <div class="sb-auth-card">
                {{ $slot }}
            </div>
            <p class="sb-auth-foot">Shyra Beautique gown rentals.</p>
        </div>
    </body>
</html>
