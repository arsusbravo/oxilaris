<aside
    class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 flex flex-col shrink-0 transform transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0 lg:z-auto"
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-700/50 shrink-0">
        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        </div>
        <span class="text-white font-bold text-base tracking-tight truncate">{{ config('app.name') }}</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" @click="mobileOpen = false"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 group
                   {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-indigo-400 group-hover:text-indigo-300' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Dashboard
        </a>

        {{-- Stores --}}
        <a href="{{ route('stores.index') }}" @click="mobileOpen = false"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 group
                   {{ request()->routeIs('stores.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('stores.*') ? 'text-white' : 'text-emerald-400 group-hover:text-emerald-300' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Stores
        </a>

        {{-- Products --}}
        <a href="{{ route('products.index') }}" @click="mobileOpen = false"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 group
                   {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('products.*') ? 'text-white' : 'text-sky-400 group-hover:text-sky-300' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Products
        </a>

        {{-- Channels --}}
        <a href="{{ route('channels.index') }}" @click="mobileOpen = false"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 group
                   {{ request()->routeIs('channels.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('channels.*') ? 'text-white' : 'text-violet-400 group-hover:text-violet-300' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Channels
        </a>

        {{-- Listings --}}
        <a href="{{ route('listings.index') }}" @click="mobileOpen = false"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 group
                   {{ request()->routeIs('listings.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('listings.*') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Listings
        </a>

        {{-- Campaigns --}}
        <a href="{{ route('campaigns.index') }}" @click="mobileOpen = false"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 group
                   {{ request()->routeIs('campaigns.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('campaigns.*') ? 'text-white' : 'text-rose-400 group-hover:text-rose-300' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            Campaigns
        </a>

        @if(auth()->user()->isAdmin())
            <div class="pt-3 mt-3 border-t border-slate-700/50">
                <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 group
                           {{ request()->routeIs('admin.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.*') ? 'text-white' : 'text-slate-400 group-hover:text-slate-300' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Admin
                </a>
            </div>
        @endif

    </nav>

    <!-- User footer -->
    <div class="border-t border-slate-700/50 p-4 shrink-0">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center shrink-0">
                <span class="text-white text-sm font-semibold leading-none">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
            </div>
            <div class="min-w-0">
                <div class="text-white text-sm font-medium truncate">{{ Auth::user()->name }}</div>
                <div class="text-slate-400 text-xs truncate">{{ Auth::user()->email }}</div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile.edit') }}"
               class="flex-1 text-center text-xs text-slate-400 hover:text-white py-1.5 rounded-md hover:bg-slate-800 transition-colors">
                Profile
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full text-xs text-slate-400 hover:text-white py-1.5 rounded-md hover:bg-slate-800 transition-colors">
                    Log Out
                </button>
            </form>
        </div>
    </div>

</aside>
