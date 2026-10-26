<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#0A2342">

    <!-- PWA PB Alfa -->
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    <!-- Ikon aplikasi untuk iPhone -->
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">


    {{-- Favicon mengikuti logo PB Alfa --}}
    @php
    $faviconLogo = ($siteSettings ?? null)?->logo_path;
    @endphp

    <link rel="icon" href="{{ $faviconLogo
          ? asset('storage/' . $faviconLogo)
          : asset('icons/icon-512.png') }}">

    <link rel="apple-touch-icon" href="{{ $faviconLogo
          ? asset('storage/' . $faviconLogo)
          : asset('icons/icon-192.png') }}">


    <meta name="description"
        content="Website PB Alfa BPS Sulawesi Barat: ranking pemain, pertandingan, kas, dan kegiatan klub.">

    <title>@yield('title', 'PB Alfa BPS Sulbar')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=8">

    {{-- CSS V3 dipakai secara global untuk navbar dan footer --}}
    <link rel="stylesheet" href="{{ asset('assets/css/home-v3.css') }}?v=5">

    @stack('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/password-toggle.css') }}?v=1">
</head>

<body>

    @php
    $branding = $siteSettings ?? null;
    @endphp

    <header class="nova-header">

        <div class="nova-header__bar">

            <a href="{{ route('home') }}" class="nova-brand">

                <span class="nova-brand__logo">
                    @if($branding?->logo_path)
                    <img src="{{ asset('storage/' . $branding->logo_path) }}" alt="Logo PB Alfa" width="45" height="45">
                    @else
                    PB
                    @endif
                </span>

                <span class="nova-brand__text">
                    <strong>PB ALFA</strong>
                    <small>BPS SULAWESI BARAT</small>
                </span>

            </a>

            <button type="button" class="nav-toggle" data-nav-toggle aria-expanded="false"
                aria-controls="site-navigation">
                <span aria-hidden="true">☰</span>
                Menu
            </button>

            <nav id="site-navigation" class="site-nav" data-nav-links aria-label="Navigasi utama">

                <a href="{{ route('home') }}" class="nova-nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                    @if(request()->routeIs('home')) aria-current="page" @endif
                    >
                    Beranda
                </a>

                <a href="{{ route('players.index') }}"
                    class="nova-nav-link {{ request()->routeIs('players.*') ? 'is-active' : '' }}"
                    @if(request()->routeIs('players.*')) aria-current="page" @endif
                    >
                    Pemain
                </a>

                <a href="{{ route('matches.index') }}"
                    class="nova-nav-link {{ request()->routeIs('matches.*') ? 'is-active' : '' }}"
                    @if(request()->routeIs('matches.*')) aria-current="page" @endif
                    >
                    Pertandingan
                </a>

                <a href="{{ route('tournaments.index') }}"
                    class="nova-nav-link {{ request()->routeIs('tournaments.*') ? 'is-active' : '' }}"
                    @if(request()->routeIs('tournaments.*'))
                    aria-current="page"
                    @endif>
                    Turnamen
                </a>

                <a href="{{ route('cash.index') }}"
                    class="nova-nav-link {{ request()->routeIs('cash.*') ? 'is-active' : '' }}"
                    @if(request()->routeIs('cash.*')) aria-current="page" @endif
                    >
                    Kas
                </a>

                @auth

                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.index') }}"
                    class="nova-nav-link {{ request()->routeIs('admin.*') ? 'is-active' : '' }}"
                    @if(request()->routeIs('admin.*')) aria-current="page" @endif
                    >
                    Panel Admin
                </a>
                @endif

                <a href="{{ route('profile.index') }}"
                    class="nova-nav-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"
                    @if(request()->routeIs('profile.*')) aria-current="page" @endif
                    >
                    Profil Saya
                </a>

                <div class="nova-nav-account">

                    <form method="POST" action="{{ route('logout') }}" data-single-submit>
                        @csrf

                        <button type="submit" class="nova-nav-logout">
                            Keluar
                        </button>
                    </form>
                </div>

                @else

                <a href="{{ route('login') }}" class="nova-nav-login">
                    Masuk ↗
                </a>

                @endauth

            </nav>

        </div>

    </header>


    <main class="site-main">

        @if(session('success'))
        <div class="alert-success" role="status">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert-error" role="alert">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')

    </main>


    {{-- Popup Global --}}

    <dialog id="pb-dialog" class="pb-dialog" aria-labelledby="pb-dialog-title" aria-describedby="pb-dialog-message">
        <div class="pb-dialog__content">

            <div class="pb-dialog__icon" aria-hidden="true">
                <span id="pb-dialog-symbol">!</span>
            </div>

            <div class="pb-dialog__heading">
                <h2 id="pb-dialog-title">
                    Konfirmasi
                </h2>

                <p id="pb-dialog-message"></p>
            </div>

            <ul id="pb-dialog-errors" hidden></ul>

            <div class="pb-dialog__actions">

                <button type="button" id="pb-dialog-cancel" class="pb-dialog__cancel">
                    Batal
                </button>

                <button type="button" id="pb-dialog-ok" class="pb-dialog__ok">
                    Mengerti
                </button>

            </div>

        </div>
    </dialog>

    {{-- Pesan gagal dari Laravel --}}

    @if($errors->any() || session('error'))
    <div id="pb-dialog-server-errors" hidden>

        @if(session('error'))
        <p data-pb-error>
            {{ session('error') }}
        </p>
        @endif

        @foreach($errors->all() as $message)
        <p data-pb-error>
            {{ $message }}
        </p>
        @endforeach

    </div>
    @endif

    <footer class="nova-footer">
        <strong>PB ALFA · BPS SULAWESI BARAT</strong>

        <div>
            Bersama di Lapangan, Solid dalam Kebersamaan.
        </div>

        <small>
            &copy; {{ date('Y') }} PB Alfa.
            All Rights Reserved.
        </small>
    </footer>


    <script src="{{ asset('assets/js/site.js') }}?v=8" defer></script>
    <script src="{{ asset('assets/js/password-toggle.js') }}?v=1" defer></script>

    @stack('scripts')

</body>

</html>