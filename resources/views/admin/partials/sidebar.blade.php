@php

/*
|--------------------------------------------------------------------------
| NAVIGASI ADMIN PB ALFA
|--------------------------------------------------------------------------
| Seluruh modul menggunakan route khusus admin.
| Menu aktif mengikuti halaman index, tambah, dan edit.
|--------------------------------------------------------------------------
*/

$navGroups = [

[
'label' => 'MENU UTAMA',

'items' => [

[
'title' => 'Dashboard',
'route' => 'admin.index',
'active' => ['admin.index'],
'icon' => 'dashboard',
],

[
'title' => 'Data Pemain',
'route' => 'admin.players.index',
'active' => [
'admin.players.index',
'admin.players.create',
'admin.players.edit',
],
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

],
],

[
'label' => 'PENGELOLAAN SISTEM',

'items' => [

[
'title' => 'Pengguna & Role',
'route' => 'admin.users',
'active' => ['admin.users*'],
'icon' => 'shield',
],

[
'title' => 'Pemain Nonaktif',
'route' => 'admin.players.inactive',
'active' => [
'admin.players.inactive',
'admin.players.restore',
],
'icon' => 'user-x',
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

],
],

];

@endphp


<aside id="admin-sidebar" class="admin-sidebar" aria-label="Navigasi administrator">

    {{-- =====================================
    IDENTITAS KLUB
    ====================================== --}}

    <a href="{{ route('admin.index') }}" class="admin-brand" aria-label="Dashboard PB Alfa">

        <span class="admin-brand__logo">

            @if(($siteSettings ?? null)?->logo_path)

            <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="Logo PB Alfa" width="43" height="43">

            @else

            PB

            @endif

        </span>

        <span class="admin-brand__text">

            <strong>PB ALFA</strong>

            <small>BPS SULAWESI BARAT</small>

        </span>

    </a>


    {{-- =====================================
    KONTEN NAVIGASI
    BAGIAN INI YANG DAPAT DI-SCROLL
    ====================================== --}}

    <div class="admin-sidebar__content">

        <div class="admin-sidebar__caption">
            ADMINISTRATION PANEL
        </div>

        <nav class="admin-nav" aria-label="Menu pengelolaan admin">

            @foreach($navGroups as $group)

            <div class="admin-nav__group">

                <span class="admin-nav__label">
                    {{ $group['label'] }}
                </span>

                @foreach($group['items'] as $item)

                @php
                $active = request()->routeIs(
                ...$item['active']
                );
                @endphp

                <a href="{{ route($item['route']) }}" class="admin-nav__link {{ $active ? 'is-active' : '' }}"
                    @if($active) aria-current="page" @endif>

                    @include(
                    'admin.partials.nav-icon',
                    ['name' => $item['icon']]
                    )

                    <span class="admin-nav__text">
                        {{ $item['title'] }}
                    </span>

                </a>

                @endforeach

            </div>

            @endforeach

        </nav>

    </div>


    {{-- =====================================
    FOOTER SIDEBAR
    SELALU BERADA DI BAWAH
    ====================================== --}}

    <div class="admin-sidebar__footer">

        {{-- Lihat Website --}}

        <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="admin-sidebar__website">

            @include(
            'admin.partials.nav-icon',
            ['name' => 'globe']
            )

            <span>
                Lihat Website
            </span>

        </a>


        {{-- Informasi Akun --}}

        <div class="admin-sidebar__account">

            <span class="admin-sidebar__account-avatar" aria-hidden="true">

                {{ mb_strtoupper(
                mb_substr(
                auth()->user()->name,
                0,
                1
                )
                ) }}

            </span>

            <div class="admin-sidebar__account-info">

                <strong>
                    {{ auth()->user()->name }}
                </strong>

                <small>
                    Administrator
                </small>

            </div>

        </div>


        {{-- Logout --}}

        <form method="POST" action="{{ route('logout') }}" data-single-submit>

            @csrf

            <button type="submit" class="admin-sidebar__logout">

                @include(
                'admin.partials.nav-icon',
                ['name' => 'logout']
                )

                <span>
                    Keluar dari Akun
                </span>

            </button>

        </form>

    </div>

</aside>