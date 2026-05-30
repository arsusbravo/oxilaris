<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Admin — {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" x-data="{ mobileOpen: false }">
        <div class="flex h-screen overflow-hidden bg-slate-50">

            {{-- Sidebar --}}
            <aside
                class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 flex flex-col shrink-0 transform transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0 lg:z-auto"
                :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'">

                <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-700/50 shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-white font-bold text-sm truncate">{{ config('app.name') }}</div>
                        <div class="text-red-400 text-xs font-medium">Admin Panel</div>
                    </div>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-0.5">
                    <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                              {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-400' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" @click="mobileOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                              {{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-800' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-gray-400' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Users
                    </a>

                    <div class="pt-3 mt-3 border-t border-gray-700/50">
                        <a href="{{ route('dashboard') }}" @click="mobileOpen = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-800 transition-colors duration-150">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to App
                        </a>
                    </div>
                </nav>

                <div class="border-t border-gray-700/50 p-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center shrink-0">
                            <span class="text-white text-xs font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-white text-sm font-medium truncate">{{ Auth::user()->name }}</div>
                            <div class="text-gray-400 text-xs truncate">Administrator</div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex-1 flex flex-col overflow-hidden min-w-0">

                {{-- Mobile top bar --}}
                <div class="lg:hidden flex items-center justify-between bg-gray-900 px-4 py-3 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-md bg-red-600 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="text-white font-bold text-sm">Admin</span>
                    </div>
                    <button @click="mobileOpen = true" class="text-gray-400 hover:text-white p-1.5 rounded-md hover:bg-gray-800 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shrink-0">
                    <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Admin' }}</h1>
                    <span class="text-sm text-gray-500 hidden sm:block">{{ Auth::user()->name }}</span>
                </header>

                <main class="flex-1 overflow-y-auto p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- Mobile backdrop --}}
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
