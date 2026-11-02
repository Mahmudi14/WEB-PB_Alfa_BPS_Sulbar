<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="theme-color" content="#102D49">

    <title>@yield('title', 'Panel Admin - PB Alfa')</title>

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

    <link rel="stylesheet" href="{{ asset('assets/css/admin-v4.css') }}?v=1">

    @stack('styles')
</head>

<body class="a4-body">

    <div class="a4-app">

        @include('admin.partials.sidebar')

        <button type="button" class="a4-backdrop" data-a4-backdrop aria-label="Tutup navigasi" tabindex="-1">
        </button>

        <div class="a4-main">

            @include('admin.partials.topbar')

            <main class="site-main a4-content">

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

    <script src="{{ asset('assets/js/site.js') }}?v=8" defer></script>

    <script src="{{ asset('assets/js/admin-v4.js') }}?v=1" defer></script>

    @stack('scripts')

</body>

</html>