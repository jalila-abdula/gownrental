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
    <body class="font-sans antialiased">
        <div class="sb-app-shell" x-data="{ sidebarOpen: false }">
            @include('layouts.navigation')
            <div class="sb-main-column">
                <header class="sb-worktop">
                    <button class="sb-mobile-toggle" @click="sidebarOpen=true" aria-label="Open navigation"><span></span><span></span><span></span></button>
                    <div class="sb-worktop-context"><span class="sb-kicker">SHYRA BEAUTIQUE</span><b>{{ ucfirst(auth()->user()->role) }} studio</b></div>
                    <div class="sb-worktop-right"><span class="sb-worktop-date">{{ now()->format('l, F j') }}</span><span class="sb-worktop-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span class="sb-worktop-name">{{ auth()->user()->name }}</span></div>
                </header>
                @isset($header)
                    <div class="sb-page-heading">{{ $header }}</div>
                @endisset
                <main class="sb-main-content">
                {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
