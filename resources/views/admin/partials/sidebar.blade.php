@php
$menus = [
[
'title' => 'Dashboard',
'route' => 'admin.index',
'active' => 'admin.index',
'icon' => 'dashboard',
],
[
'title' => 'Data Pemain',
'route' => 'admin.players.index',
'active' => 'admin.players.*',
'icon' => 'users',
],
[
'title' => 'Pertandingan',
'route' => 'admin.matches.index',
'active' => 'admin.matches.*',
'icon' => 'match',
],
[
'title' => 'Turnamen',
'route' => 'admin.tournaments.index',
'active' => 'admin.tournaments.*',
'icon' => 'trophy',
],
[
'title' => 'Keuangan',
'route' => 'admin.cash.index',
'active' => 'admin.cash.*',
'icon' => 'wallet',
],
[
'title' => 'Galeri Klub',
'route' => 'admin.gallery.index',
'active' => 'admin.gallery.*',
'icon' => 'image',
],
[
'title' => 'Pengaturan Website',
'route' => 'admin.settings.edit',
'active' => 'admin.settings.*',
'icon' => 'settings',
],
];
@endphp

<aside id="a4-sidebar" class="a4-sidebar" aria-label="Navigasi admin">
    <a href="{{ route('admin.index') }}" class="a4-brand">

        <span class="a4-brand__logo">
            @if(($siteSettings ?? null)?->logo_path)
            <img src="{{ asset('storage/' . $siteSettings->logo_path) }}" alt="Logo PB Alfa">
            @else
            PB
            @endif
        </span>

        <span class="a4-brand__info">
            <strong>PB ALFA</strong>
            <small>BPS SULAWESI BARAT</small>
        </span>

    </a>

    <div class="a4-nav-scroll">

        <p class="a4-nav-label">MENU ADMINISTRATOR</p>

        <nav class="a4-nav" aria-label="Menu utama">

            @foreach($menus as $menu)
            @php
            $active = request()->routeIs(
            $menu['active']
            );
            @endphp

            <a href="{{ route($menu['route']) }}" class="a4-nav__link {{ $active ? 'is-active' : '' }}" @if($active)
                aria-current="page" @endif>
                @include(
                'admin.partials.nav-icon',
                ['name' => $menu['icon']]
                )

                <span>{{ $menu['title'] }}</span>
            </a>
            @endforeach

        </nav>

    </div>

    <div class="a4-sidebar__footer">

        <a href="{{ route('home') }}" class="a4-footer-link" target="_blank" rel="noopener noreferrer">
            @include(
            'admin.partials.nav-icon',
            ['name' => 'globe']
            )
            <span>Lihat Website</span>
        </a>

        <div class="a4-account">
            <span class="a4-account__avatar">
                {{ mb_strtoupper(
                mb_substr(auth()->user()->name, 0, 1)
                ) }}
            </span>

            <span class="a4-account__info">
                <strong>{{ auth()->user()->name }}</strong>
                <small>Administrator</small>
            </span>
        </div>

        <form method="POST" action="{{ route('logout') }}" data-single-submit>
            @csrf

            <button type="submit" class="a4-logout">
                @include(
                'admin.partials.nav-icon',
                ['name' => 'logout']
                )
                <span>Keluar dari Akun</span>
            </button>
        </form>

    </div>

</aside>