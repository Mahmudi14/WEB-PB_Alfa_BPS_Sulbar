
@extends('layouts.app')

@section('title', 'Panel Admin - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v=1">
@endpush

@section('content')

<div class="adm-page">

    <header class="adm-hero">
        <div class="adm-hero__content">
            <span class="adm-eyebrow">
                PB ALFA / ADMINISTRATION
            </span>

            <h1>PANEL <span>ADMINISTRATOR.</span></h1>

            <p>
                Selamat datang,
                <strong>{{ auth()->user()->name }}</strong>.
                Kelola PB Alfa dari satu tempat.
            </p>
        </div>

        <span class="adm-hero__decoration" aria-hidden="true">
            PB
        </span>
    </header>

    {{-- Menu --}}

    <section class="adm-section">

        <div class="adm-section__heading">
            <div>
                <span class="adm-eyebrow adm-eyebrow--green">
                    MANAGEMENT CENTER
                </span>

                <h2>Menu Pengelolaan</h2>
            </div>

            <span class="adm-count">7 Menu</span>
        </div>

        <div class="adm-menu-grid">

            <a href="{{ route('players.index') }}"
               class="adm-menu-card">

                <span class="adm-menu-card__number">01</span>

                <div class="adm-menu-card__body">
                    <h3>Data Pemain</h3>
                    <p>Tambah, edit, dan kelola pemain.</p>
                </div>

                <span class="adm-menu-card__arrow" aria-hidden="true">
                    ↗
                </span>
            </a>

            <a href="{{ route('matches.index') }}"
               class="adm-menu-card">

                <span class="adm-menu-card__number">02</span>

                <div class="adm-menu-card__body">
                    <h3>Pertandingan</h3>
                    <p>Catat dan kelola hasil pertandingan.</p>
                </div>

                <span class="adm-menu-card__arrow" aria-hidden="true">
                    ↗
                </span>
            </a>

            <a href="{{ route('cash.index') }}"
               class="adm-menu-card">

                <span class="adm-menu-card__number">03</span>

                <div class="adm-menu-card__body">
                    <h3>Keuangan</h3>
                    <p>Kelola pemasukan dan pengeluaran kas.</p>
                </div>

                <span class="adm-menu-card__arrow" aria-hidden="true">
                    ↗
                </span>
            </a>

            <a href="{{ route('admin.users') }}"
               class="adm-menu-card">

                <span class="adm-menu-card__number">04</span>

                <div class="adm-menu-card__body">
                    <h3>Pengguna dan Role</h3>
                    <p>Atur hak akses Pemain dan Pengelola.</p>
                </div>

                <span class="adm-menu-card__arrow" aria-hidden="true">
                    ↗
                </span>
            </a>

            <a href="{{ route('admin.players.inactive') }}"
               class="adm-menu-card">

                <span class="adm-menu-card__number">05</span>

                <div class="adm-menu-card__body">
                    <h3>Pemain Nonaktif</h3>
                    <p>Aktifkan kembali pemain yang dihapus.</p>
                </div>

                <span class="adm-menu-card__arrow" aria-hidden="true">
                    ↗
                </span>
            </a>

            <a href="{{ route('admin.settings.edit') }}"
               class="adm-menu-card">

                <span class="adm-menu-card__number">06</span>

                <div class="adm-menu-card__body">
                    <h3>Pengaturan Website</h3>
                    <p>Perbarui logo dan informasi klub.</p>
                </div>

                <span class="adm-menu-card__arrow" aria-hidden="true">
                    ↗
                </span>
            </a>

            <a href="{{ route('admin.gallery.index') }}"
               class="adm-menu-card">

                <span class="adm-menu-card__number">07</span>

                <div class="adm-menu-card__body">
                    <h3>Galeri</h3>
                    <p>Unggah dan kelola foto kegiatan.</p>
                </div>

                <span class="adm-menu-card__arrow" aria-hidden="true">
                    ↗
                </span>
            </a>

        </div>

    </section>

</div>

@endsection
