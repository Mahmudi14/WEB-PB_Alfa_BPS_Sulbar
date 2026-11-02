<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#102D49">

    <title>@yield('title', 'Admin - PB Alfa')</title>

    @php
    $logo = ($siteSettings ?? null)?->logo_path;
    @endphp

    <link rel="icon" href="{{ $logo
              ? asset('storage/' . $logo)
              : asset('icons/icon-512.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=8">

    <link rel="stylesheet" href="{{ asset('assets/css/admin-layout.css') }}?v=1">

    @stack('styles')
</head>

<body class="admin-body">

    @php
    $pageTitle = match (true) {
    request()->routeIs('admin.users*') =>
    'Pengguna & Role',

    request()->routeIs('admin.players.*') =>
    'Pemain Nonaktif',

    request()->routeIs('admin.settings.*') =>
    'Pengaturan Website',

    request()->routeIs('admin.gallery.*') =>
    'Galeri Klub',

    default => 'Dashboard',
    };
    @endphp

    <div class="admin-app">

        @include('admin.partials.sidebar')

        <button type="button" class="admin-backdrop" data-admin-backdrop tabindex="-1" aria-label="Tutup sidebar">
        </button>

        <div class="admin-main">

            <header class="admin-topbar">

                <div class="admin-topbar__left">

                    <button type="button" class="admin-menu-toggle" data-admin-toggle
                        aria-label="Buka menu administrator" aria-controls="admin-sidebar" aria-expanded="false">

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div>
                        <span class="admin-topbar__eyebrow">
                            PB ALFA / ADMINISTRATOR
                        </span>

                        <h1 class="admin-topbar__title">
                            {{ $pageTitle }}
                        </h1>
                    </div>

                </div>

                <div class="admin-topbar__user">

                    <div class="admin-topbar__identity">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>Administrator</span>
                    </div>

                    <span class="admin-topbar__avatar" aria-hidden="true">
                        {{ mb_strtoupper(
                        mb_substr(auth()->user()->name, 0, 1)
                        ) }}
                    </span>

                </div>

            </header>

            <main class="site-main admin-content">

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

        </div>

    </div>

    @include('admin.partials.dialog')

    <script src="{{ asset('assets/js/site.js') }}?v=8" defer>
    </script>

    <script src="{{ asset('assets/js/admin-sidebar.js') }}?v=1" defer>
    </script>

    @stack('scripts')

</body>

</html>