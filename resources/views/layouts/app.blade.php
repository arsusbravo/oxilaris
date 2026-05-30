<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" x-data="{ mobileOpen: false }">
        <div class="flex h-screen overflow-hidden bg-slate-50">

            @include('layouts.navigation')

            <!-- Right side: mobile bar + header + content -->
            <div class="flex-1 flex flex-col overflow-hidden min-w-0">

                <!-- Mobile top bar -->
                <div class="lg:hidden flex items-center justify-between bg-slate-900 px-4 py-3 shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-md bg-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-white font-bold text-sm">{{ config('app.name') }}</span>
                    </a>
                    <button @click="mobileOpen = true" class="text-slate-400 hover:text-white p-1.5 rounded-md hover:bg-slate-800 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b border-slate-200 px-6 py-4 shrink-0">
                        {{ $header }}
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Mobile sidebar backdrop -->
        <div x-show="mobileOpen"
             @click="mobileOpen = false"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-20 lg:hidden"
             style="display:none">
        </div>
    </body>
</html>
