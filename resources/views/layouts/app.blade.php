
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#0A2342">

    <meta name="description"
          content="Website PB Alfa BPS Sulawesi Barat: ranking pemain, pertandingan, kas, dan kegiatan klub.">

    <title>@yield('title', 'PB Alfa BPS Sulbar')</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="{{ asset('assets/css/site.css') }}?v=7"
    >

    {{-- CSS V3 dipakai secara global untuk navbar dan footer --}}
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/home-v3.css') }}?v=3"
    >

    @stack('styles')
</head>

<body>

@php
    $branding = $siteSettings ?? null;
@endphp

<header class="nova-header">

    <div class="nova-header__bar">

        <a href="{{ route('home') }}"
           class="nova-brand">

            <span class="nova-brand__logo">
                @if($branding?->logo_path)
                    <img
                        src="{{ asset('storage/' . $branding->logo_path) }}"
                        alt="Logo PB Alfa"
                        width="45"
                        height="45"
                    >
                @else
                    PB
                @endif
            </span>

            <span class="nova-brand__text">
                <strong>PB ALFA</strong>
                <small>BPS SULAWESI BARAT</small>
            </span>

        </a>

        <button
            type="button"
            class="nav-toggle"
            data-nav-toggle
            aria-expanded="false"
            aria-controls="site-navigation"
        >
            <span aria-hidden="true">☰</span>
            Menu
        </button>

        <nav
            id="site-navigation"
            class="site-nav"
            data-nav-links
            aria-label="Navigasi utama"
        >

            <a
                href="{{ route('home') }}"
                class="nova-nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                @if(request()->routeIs('home')) aria-current="page" @endif
            >
                Beranda
            </a>

            <a
                href="{{ route('players.index') }}"
                class="nova-nav-link {{ request()->routeIs('players.*') ? 'is-active' : '' }}"
                @if(request()->routeIs('players.*')) aria-current="page" @endif
            >
                Pemain
            </a>

            <a
                href="{{ route('matches.index') }}"
                class="nova-nav-link {{ request()->routeIs('matches.*') ? 'is-active' : '' }}"
                @if(request()->routeIs('matches.*')) aria-current="page" @endif
            >
                Pertandingan
            </a>

            <a
                href="{{ route('cash.index') }}"
                class="nova-nav-link {{ request()->routeIs('cash.*') ? 'is-active' : '' }}"
                @if(request()->routeIs('cash.*')) aria-current="page" @endif
            >
                Kas
            </a>

            @auth

                @if(auth()->user()->isAdmin())
                    <a
                        href="{{ route('admin.index') }}"
                        class="nova-nav-link {{ request()->routeIs('admin.*') ? 'is-active' : '' }}"
                        @if(request()->routeIs('admin.*')) aria-current="page" @endif
                    >
                        Panel Admin
                    </a>
                @endif

                <div class="nova-nav-account">

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        data-single-submit
                    >
                        @csrf

                        <button type="submit"
                                class="nova-nav-logout">
                            Keluar
                        </button>
                    </form>
                </div>

            @else

                <a href="{{ route('login') }}"
                   class="nova-nav-login">
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


<script
    src="{{ asset('assets/js/site.js') }}?v=7"
    defer
></script>

@stack('scripts')

</body>
</html>
