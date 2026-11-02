@php

$adminMenus = [
[
'title' => 'Dashboard',
'route' => 'admin.index',
'active' => ['admin.index'],
'icon' => 'dashboard',
],
[
'title' => 'Data Pemain',
'route' => 'admin.players.index',
'active' => ['admin.players.*'],
'icon' => 'users',
],
[
'title' => 'Pertandingan',
'route' => 'admin.matches.index',
'active' => ['admin.matches.*'],
'icon' => 'match',
],
[
'title' => 'Turnamen',
'route' => 'admin.tournaments.index',
'active' => ['admin.tournaments.*'],
'icon' => 'trophy',
],
[
'title' => 'Keuangan',
'route' => 'admin.cash.index',
'active' => ['admin.cash.*'],
'icon' => 'wallet',
],
[
'title' => 'Galeri Klub',
'route' => 'admin.gallery.index',
'active' => ['admin.gallery.*'],
'icon' => 'image',
],
[
'title' => 'Pengaturan Website',
'route' => 'admin.settings.edit',
'active' => ['admin.settings.*'],
'icon' => 'settings',
],
];

@endphp

<aside id="admin-sidebar" class="adm-sidebar" aria-label="Navigasi administrator">

    <a href="{{ route('admin.index') }}" class="adm-brand">

        <span class="adm-brand__logo">

            @if(($siteSettings ?? null)?->logo_path)

            <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="Logo PB Alfa" width="44" height="44">

            @else
            PB
            @endif

        </span>

        <span class="adm-brand__identity">
            <strong>PB ALFA</strong>
            <small>BPS SULAWESI BARAT</small>
        </span>

    </a>


    {{-- NAVIGASI --}}

    <div class="adm-sidebar__scroll">

        <div class="adm-sidebar__section-label">
            MENU ADMINISTRATOR
        </div>

        <nav class="adm-nav" aria-label="Menu utama admin">

            @foreach($adminMenus as $menu)

            @php
            $available =
            \Illuminate\Support\Facades\Route::has(
            $menu['route']
            );

            $active = request()->routeIs(
            ...$menu['active']
            );
            @endphp

            @if($available)

            <a href="{{ route($menu['route']) }}" class="adm-nav__link {{ $active ? 'is-active' : '' }}" @if($active)
                aria-current="page" @endif>

                @include(
                'admin.partials.nav-icon',
                ['name' => $menu['icon']]
                )

                <span class="adm-nav__text">
                    {{ $menu['title'] }}
                </span>

            </a>

            @else

            <span class="adm-nav__link adm-nav__link--disabled" aria-disabled="true" title="Modul sedang dikembangkan">

                @include(
                'admin.partials.nav-icon',
                ['name' => $menu['icon']]
                )

                <span class="adm-nav__text">
                    {{ $menu['title'] }}
                </span>

                <small class="adm-nav__soon">
                    Segera
                </small>

            </span>

            @endif

            @endforeach

        </nav>

    </div>


    {{-- FOOTER SIDEBAR --}}

    <div class="adm-sidebar__footer">

        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="adm-sidebar__website">

            @include(
            'admin.partials.nav-icon',
            ['name' => 'globe']
            )

            <span>Lihat Website</span>

            <svg class="adm-sidebar__external" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M13 5h6v6" />
                <path d="M19 5l-9 9" />
                <path d="M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5" />
            </svg>

        </a>

    </div>

</aside>