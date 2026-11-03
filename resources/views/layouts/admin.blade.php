<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#102D49">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Panel Admin - PB Alfa')</title>

    @php
    $adminLogo = ($siteSettings ?? null)?->logo_path;
    @endphp

    <link rel="icon" href="{{ $adminLogo
            ? asset('storage/' . $adminLogo)
            : asset('icons/icon-512.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
        href="{{ asset('assets/css/admin.css') }}?v={{ filemtime(public_path('assets/css/admin.css')) }}">

    @stack('styles')

</head>

<body class="adm-body">

    <div class="adm-app">

        @include('admin.partials.sidebar')

        <button type="button" class="adm-backdrop" data-admin-backdrop tabindex="-1"
            aria-label="Tutup sidebar"></button>

        <div class="adm-main">

            @include('admin.partials.topbar')

            <main id="admin-content" class="adm-content">

                @if(session('success'))
                <div class="adm-alert adm-alert--success" role="status">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="adm-alert adm-alert--error" role="alert">
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')

            </main>

        </div>

    </div>

    @include('admin.partials.dialog')

    <script src="{{ asset('assets/js/admin.js') }}?v=1" defer></script>

    @stack('scripts')

</body>

</html>