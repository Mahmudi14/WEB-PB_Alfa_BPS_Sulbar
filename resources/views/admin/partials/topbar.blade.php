@php

$adminPageTitle = match (true) {

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

default => 'Dashboard',

};

@endphp

<header class="adm-topbar">

    <div class="adm-topbar__left">

        <button type="button" class="adm-menu-toggle" data-admin-toggle aria-label="Buka menu administrator"
            aria-controls="admin-sidebar" aria-expanded="false">

            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>

        </button>

        <div class="adm-topbar__heading">

            <span class="adm-topbar__eyebrow">
                PB ALFA / ADMINISTRATOR
            </span>

            <h1>
                @yield('page_title', $adminPageTitle)
            </h1>

        </div>

    </div>


    {{-- DROPDOWN PROFIL --}}

    <div class="adm-topbar__right">

        <div class="adm-profile" data-admin-profile>

            <button type="button" class="adm-profile__trigger" data-admin-profile-toggle aria-label="Buka menu profil"
                aria-haspopup="true" aria-controls="admin-profile-dropdown" aria-expanded="false">

                <span class="adm-profile__avatar">
                    {{ mb_strtoupper(
                    mb_substr(auth()->user()->name, 0, 1)
                    ) }}
                </span>

                <span class="adm-profile__trigger-info">

                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <small>Administrator</small>

                </span>

                <svg class="adm-profile__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6" />
                </svg>

            </button>


            <div id="admin-profile-dropdown" class="adm-profile__dropdown" data-admin-profile-menu hidden>

                <div class="adm-profile__header">

                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <span>
                        {{ auth()->user()->email }}
                    </span>

                    <small>Administrator PB Alfa</small>

                </div>

                <div class="adm-profile__links">

                    <a href="{{ route('profile.index') }}" class="adm-profile__item">

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 21a8 8 0 0 1 16 0" />
                        </svg>

                        <span>Profil Saya</span>

                    </a>

                    <form method="POST" action="{{ route('logout') }}" data-single-submit>

                        @csrf

                        <button type="submit" class="adm-profile__item adm-profile__item--logout">

                            @include(
                            'admin.partials.nav-icon',
                            ['name' => 'logout']
                            )

                            <span>Keluar dari Akun</span>

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</header>