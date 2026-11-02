@extends('layouts.admin')

@section('title', 'Dashboard Admin - PB Alfa')

@section('content')

<div class="admin-dashboard">

    {{-- Selamat datang --}}

    <section class="admin-welcome">

        <span class="admin-welcome__label">
            PB ALFA / MANAGEMENT CENTER
        </span>

        <h2>
            Selamat Datang,
            {{ auth()->user()->name }}!
        </h2>

        <p>
            Kelola data pemain, pertandingan, turnamen,
            keuangan, dan pengaturan PB Alfa BPS
            Sulawesi Barat dari satu panel administrator.
        </p>

    </section>


    {{-- Statistik utama --}}

    <section aria-labelledby="admin-stats-title">

        <div class="admin-block-heading">
            <div>
                <h2 id="admin-stats-title">
                    Ringkasan Klub
                </h2>

                <p>
                    Informasi terbaru berdasarkan
                    data tersimpan.
                </p>
            </div>
        </div>

        <div class="admin-stats">

            {{-- Pemain --}}

            <article class="admin-stat">

                <span class="admin-stat__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'users']
                    )
                </span>

                <span class="admin-stat__label">
                    Total Pemain Aktif
                </span>

                <strong class="admin-stat__value">
                    {{ number_format($playerCount, 0, ',', '.') }}
                </strong>

                <span class="admin-stat__note">
                    Pemain yang belum dinonaktifkan
                </span>

            </article>


            {{-- Pertandingan --}}

            <article class="admin-stat admin-stat--green">

                <span class="admin-stat__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'match']
                    )
                </span>

                <span class="admin-stat__label">
                    Pertandingan Reguler
                </span>

                <strong class="admin-stat__value">
                    {{ number_format(
                    $regularMatchCount, 0, ',', '.'
                    ) }}
                </strong>

                <span class="admin-stat__note">
                    Termasuk pertandingan pending
                </span>

            </article>


            {{-- Turnamen --}}

            <article class="admin-stat admin-stat--orange">

                <span class="admin-stat__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'trophy']
                    )
                </span>

                <span class="admin-stat__label">
                    Total Turnamen
                </span>

                <strong class="admin-stat__value">
                    {{ number_format(
                    $tournamentCount, 0, ',', '.'
                    ) }}
                </strong>

                <span class="admin-stat__note">
                    Seluruh status Liga dan Cup
                </span>

            </article>


            {{-- Keuangan --}}

            <article class="admin-stat">

                <span class="admin-stat__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'wallet']
                    )
                </span>

                <span class="admin-stat__label">
                    Saldo Kas
                </span>

                <strong class="admin-stat__value
                    admin-stat__value--cash
                    {{ $cashBalance < 0 ? 'is-negative' : '' }}">

                    Rp {{ number_format(
                    $cashBalance, 0, ',', '.'
                    ) }}

                </strong>

                <span class="admin-stat__note">
                    Pemasukan dikurangi pengeluaran
                </span>

            </article>

        </div>

    </section>


    {{-- Akses cepat --}}

    <section class="admin-panel" aria-labelledby="admin-shortcuts-title">

        <div class="admin-block-heading">
            <div>
                <h2 id="admin-shortcuts-title">
                    Akses Cepat
                </h2>

                <p>
                    Pilih aktivitas pengelolaan sistem.
                </p>
            </div>
        </div>

        <div class="admin-shortcuts">

            <a href="{{ route('admin.users') }}" class="admin-shortcut">

                <span class="admin-shortcut__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'shield']
                    )
                </span>

                <span class="admin-shortcut__text">
                    <strong>Pengguna & Role</strong>
                    <small>
                        Kelola hak akses dan reset password.
                    </small>
                </span>

                <span class="admin-shortcut__arrow" aria-hidden="true">›</span>

            </a>


            <a href="{{ route('admin.players.inactive') }}" class="admin-shortcut">

                <span class="admin-shortcut__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'user-x']
                    )
                </span>

                <span class="admin-shortcut__text">
                    <strong>Pemain Nonaktif</strong>
                    <small>
                        Pulihkan pemain yang dinonaktifkan.
                    </small>
                </span>

                <span class="admin-shortcut__arrow" aria-hidden="true">›</span>

            </a>


            <a href="{{ route('admin.gallery.index') }}" class="admin-shortcut">

                <span class="admin-shortcut__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'image']
                    )
                </span>

                <span class="admin-shortcut__text">
                    <strong>Galeri Klub</strong>
                    <small>
                        {{ $galleryCount }} foto tersimpan.
                        Kelola dokumentasi kegiatan.
                    </small>
                </span>

                <span class="admin-shortcut__arrow" aria-hidden="true">›</span>

            </a>


            <a href="{{ route('admin.settings.edit') }}" class="admin-shortcut">

                <span class="admin-shortcut__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'settings']
                    )
                </span>

                <span class="admin-shortcut__text">
                    <strong>Pengaturan Website</strong>
                    <small>
                        Atur logo dan informasi klub.
                    </small>
                </span>

                <span class="admin-shortcut__arrow" aria-hidden="true">›</span>

            </a>

        </div>

    </section>

</div>

@endsection