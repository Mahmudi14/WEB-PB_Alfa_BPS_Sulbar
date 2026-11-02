@php
$pageTitle = match (true) {
request()->routeIs('admin.players.inactive')
=> 'Data Pemain',

request()->routeIs('admin.players.*')
=> 'Data Pemain',

request()->routeIs('admin.matches.*')
=> 'Pertandingan',

request()->routeIs('admin.tournaments.*')
=> 'Turnamen',

request()->routeIs('admin.cash.*')
=> 'Keuangan',

request()->routeIs('admin.gallery.*')
=> 'Galeri Klub',

request()->routeIs('admin.settings.*')
=> 'Pengaturan Website',

request()->routeIs('admin.users*')
=> 'Pengguna & Role',

default => 'Dashboard',
};
@endphp

<header class="a4-topbar">

    <div class="a4-topbar__left">

        <button type="button" class="a4-menu-toggle" data-a4-toggle aria-controls="a4-sidebar" aria-expanded="false"
            aria-label="Buka navigasi">
            <span aria-hidden="true">☰</span>
        </button>

        <div>
            <span class="a4-topbar__eyebrow">
                PB ALFA / ADMINISTRATION
            </span>

            <h1>{{ $pageTitle }}</h1>
        </div>

    </div>

    <div class="a4-topbar__account">
        <span class="a4-topbar__name">
            {{ auth()->user()->name }}
        </span>

        <span class="a4-topbar__avatar" aria-hidden="true">
            {{ mb_strtoupper(
            mb_substr(auth()->user()->name, 0, 1)
            ) }}
        </span>
    </div>

</header>