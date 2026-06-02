<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OXILaris — Jual di Semua Lapak, Kelola Semuanya</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-slate-900">

    {{-- NAV --}}
    <nav class="sticky top-0 z-50 bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <img src="/images/oxilaris-icon.png" alt="OXILaris" class="h-8 w-8 object-contain">
                <span class="font-extrabold text-lg tracking-tight leading-none select-none">
                    <span style="color:#C0391A;">OXI</span><span class="text-slate-900">Laris</span>
                </span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="px-5 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
                       style="background-color:#C0391A;">
                        Buka Aplikasi
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                        Masuk
                    </a>
                    <a href="{{ route('demo') }}"
                       class="px-5 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
                       style="background-color:#C0391A;">
                        Coba Demo Gratis
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <section class="bg-slate-50 border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-6 py-24 text-center">
            <div class="flex justify-center mb-10">
                <img src="/images/oxilaris-full.png" alt="OXILaris" class="h-24 w-auto object-contain">
            </div>

            <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight leading-tight text-slate-900 mb-6">
                Jual di semua lapak.<br>
                <span style="color:#C0391A;">Tanpa ribet.</span>
            </h1>

            <p class="text-lg sm:text-xl text-slate-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                OXILaris menghubungkan produk Anda ke Tokopedia, Shopee, TikTok Shop, dan lainnya — dari satu dashboard. Tidak perlu buka banyak tab lagi.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="w-full sm:w-auto px-8 py-4 rounded-xl text-base font-bold text-white transition-all hover:opacity-90"
                       style="background-color:#C0391A;">
                        Ke Dashboard →
                    </a>
                @else
                    <a href="{{ route('demo') }}"
                       class="w-full sm:w-auto px-8 py-4 rounded-xl text-base font-bold text-white transition-all hover:opacity-90"
                       style="background-color:#C0391A;">
                        Coba Demo Gratis →
                    </a>
                @endauth
            </div>

            <p class="mt-5 text-sm text-slate-400">Tanpa kartu kredit &bull; Siap dalam hitungan menit</p>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 px-6 border-b border-slate-100">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mb-3">
                    Cara kerjanya
                </h2>
                <p class="text-slate-500 text-lg">Dari foto produk ke semua lapak dalam 3 langkah.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="text-center p-6">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-lg" style="background-color:#C0391A;">1</div>
                    <h3 class="font-bold text-slate-900 mb-2">Upload foto produk</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Foto produk Anda langsung dianalisis AI — judul, deskripsi, dan spesifikasi terisi otomatis.</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-lg" style="background-color:#C0391A;">2</div>
                    <h3 class="font-bold text-slate-900 mb-2">Hubungkan lapak</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Sambungkan Tokopedia, Shopee, TikTok Shop, dan lainnya dengan satu klik — tanpa perlu kode API.</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold text-lg" style="background-color:#C0391A;">3</div>
                    <h3 class="font-bold text-slate-900 mb-2">Push & jualan</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Pilih produk, pilih lapak, klik Push. Produk Anda langsung tayang di semua tempat sekaligus.</p>
                </div>
            </div>

            @guest
            <div class="text-center mt-8">
                <a href="{{ route('demo') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm border-2 transition-all hover:text-white"
                   style="border-color:#C0391A; color:#C0391A;"
                   onmouseover="this.style.backgroundColor='#C0391A'" onmouseout="this.style.backgroundColor='transparent'">
                    ✦ Coba sekarang — gratis, tanpa daftar
                </a>
            </div>
            @endguest
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="py-24 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mb-4">
                    Semua yang Anda butuhkan untuk <span style="color:#C0391A;">berkembang</span>
                </h2>
                <p class="text-slate-500 text-lg max-w-xl mx-auto">
                    Dari produk pertama hingga ribuan listing di banyak lapak — OXILaris siap membantu.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="rounded-2xl p-6 border border-slate-200 bg-white hover:shadow-md hover:border-slate-300 transition-all">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color:#FBE9E4;">
                        <svg class="w-5 h-5" style="color:#C0391A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Kelola Banyak Toko</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Hubungkan WooCommerce, Shopify, Magento, dan lainnya. Kelola semua toko dari satu dashboard tanpa perlu berpindah tab.
                    </p>
                </div>

                <div class="rounded-2xl p-6 border border-slate-200 bg-white hover:shadow-md hover:border-slate-300 transition-all">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color:#FBE9E4;">
                        <svg class="w-5 h-5" style="color:#C0391A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Sinkronisasi Lapak</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Kirim listing ke Tokopedia, Shopee, Lazada, dan lapak lain secara otomatis. Satu perubahan, langsung tersebar ke mana-mana.
                    </p>
                </div>

                <div class="rounded-2xl p-6 border border-slate-200 bg-white hover:shadow-md hover:border-slate-300 transition-all">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color:#FBE9E4;">
                        <svg class="w-5 h-5" style="color:#C0391A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Katalog Produk Cerdas</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Impor produk dari sumber mana pun dan biarkan AI menulis judul, deskripsi, serta atribut — dioptimalkan untuk setiap lapak.
                    </p>
                </div>

                <div class="rounded-2xl p-6 border border-slate-200 bg-white hover:shadow-md hover:border-slate-300 transition-all">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color:#FBE9E4;">
                        <svg class="w-5 h-5" style="color:#C0391A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Otomatisasi Listing</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Berhenti copy-paste manual. OXILaris memetakan produk ke format setiap lapak secara otomatis dan menjaga listing tetap sinkron.
                    </p>
                </div>

                <div class="rounded-2xl p-6 border border-slate-200 bg-white hover:shadow-md hover:border-slate-300 transition-all">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color:#FBE9E4;">
                        <svg class="w-5 h-5" style="color:#C0391A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Manajemen Kampanye</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Jalankan promosi di semua lapak sekaligus. Jadwalkan, pantau, dan optimalkan kampanye tanpa harus membuka banyak dashboard.
                    </p>
                </div>

                <div class="rounded-2xl p-6 border border-slate-200 bg-white hover:shadow-md hover:border-slate-300 transition-all">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" style="background-color:#FBE9E4;">
                        <svg class="w-5 h-5" style="color:#C0391A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">Konten Berbasis AI</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Buat judul dan deskripsi produk yang dioptimalkan SEO dalam hitungan detik. Biarkan AI bekerja keras, Anda fokus berjualan.
                    </p>
                </div>

            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-24 px-6 bg-slate-50 border-t border-slate-200">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mb-4">
                Siap menguasai lapak?
            </h2>
            <p class="text-slate-500 text-lg mb-8 leading-relaxed">
                Bergabunglah dengan penjual yang sudah berhenti berjuang melawan alatnya sendiri dan mulai tumbuh bersama OXILaris.
            </p>
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="inline-block px-10 py-4 rounded-xl text-base font-bold text-white transition-all hover:opacity-90"
                   style="background-color:#C0391A;">
                    Buka Dashboard →
                </a>
            @else
                <a href="{{ route('demo') }}"
                   class="inline-block px-10 py-4 rounded-xl text-base font-bold text-white transition-all hover:opacity-90"
                   style="background-color:#C0391A;">
                    Coba Demo Gratis →
                </a>
                <p class="mt-4 text-sm text-slate-400">Sudah punya akun? <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900 underline">Masuk</a></p>
            @endauth
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="border-t border-slate-200 py-8 px-6 bg-white">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <img src="/images/oxilaris-icon.png" alt="OXILaris" class="h-6 w-6 object-contain">
                <span class="font-extrabold text-sm tracking-tight">
                    <span style="color:#C0391A;">OXI</span><span class="text-slate-900">Laris</span>
                </span>
            </div>
            <p class="text-slate-400 text-sm">
                &copy; {{ date('Y') }} OXILaris. Hak cipta dilindungi.
            </p>
        </div>
    </footer>

</body>
</html>
