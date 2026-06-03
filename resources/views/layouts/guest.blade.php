<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OXIlaris') }}</title>
        <meta name="description" content="OXIlaris — Kelola produk dan jual di Tokopedia, Shopee, OLX dan banyak platform lainnya dari satu dashboard.">

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="/images/oxilaris-icon.png">
        <link rel="apple-touch-icon" href="/images/oxilaris-icon.png">

        <!-- Open Graph -->
        <meta property="og:title" content="OXIlaris">
        <meta property="og:description" content="Kelola produk dan jual di Tokopedia, Shopee, OLX dan banyak platform lainnya dari satu dashboard.">
        <meta property="og:image" content="{{ url('/images/oxilaris-icon.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="OXIlaris">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="OXIlaris">
        <meta name="twitter:description" content="Kelola produk dan jual di Tokopedia, Shopee, OLX dan banyak platform lainnya dari satu dashboard.">
        <meta name="twitter:image" content="{{ url('/images/oxilaris-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <img src="/images/oxilaris-full.png" alt="OXIlaris" class="h-32 w-auto object-contain">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
