<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Gratis — OXIlaris</title>
    <meta name="description" content="Coba gratis! Scan foto produk Anda dan biarkan AI OXIlaris membuat judul, deskripsi, kategori, dan spesifikasi dalam hitungan detik.">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/oxilaris-icon.png">
    <link rel="apple-touch-icon" href="/images/oxilaris-icon.png">

    <!-- Open Graph -->
    <meta property="og:title" content="Demo Gratis AI Produk — OXIlaris">
    <meta property="og:description" content="Scan foto produk, AI isi detailnya otomatis. Coba gratis sekarang — tidak perlu daftar!">
    <meta property="og:image" content="{{ url('/images/oxilaris-icon.png') }}">
    <meta property="og:url" content="{{ url('/demo') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="OXIlaris">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Demo Gratis AI Produk — OXIlaris">
    <meta name="twitter:description" content="Scan foto produk, AI isi detailnya otomatis. Coba gratis sekarang!">
    <meta name="twitter:image" content="{{ url('/images/oxilaris-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="font-sans antialiased bg-slate-50">

    {{-- Nav --}}
    <nav class="sticky top-0 z-50 bg-white border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <img src="/images/oxilaris-icon.png" alt="OXIlaris" class="h-7 w-7 object-contain">
                <span class="font-extrabold text-base tracking-tight leading-none">
                    <span style="color:#C0391A;">OXI</span><span class="text-slate-900">laris</span>
                </span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">Masuk</a>
                <a href="{{ route('register') }}"
                   class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
                   style="background-color:#C0391A;">
                    Daftar Gratis
                </a>
            </div>
        </div>
    </nav>

    {{-- Vue App --}}
    <div id="demo-app"
         data-scans-left="{{ $scansLeft }}"
         data-platforms="{{ json_encode($platforms) }}"
         data-csrf="{{ csrf_token() }}"
         data-turnstile-key="{{ config('services.turnstile.site_key') }}">
    </div>

</body>
</html>
