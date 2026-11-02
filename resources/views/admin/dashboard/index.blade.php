@extends('layouts.admin')

@section('title', 'Dashboard Admin - PB Alfa')

@section('content')

<div class="a4-page">

    <div class="a4-heading">
        <div>
            <span class="a4-eyebrow">
                OVERVIEW / PB ALFA
            </span>

            <h2>Ringkasan Klub</h2>

            <p>
                Pantau data utama dan aktivitas PB Alfa
                BPS Sulawesi Barat.
            </p>
        </div>
    </div>

    <div class="a4-stats">

        <a href="{{ route('admin.players.index') }}" class="a4-stat">

            <span class="a4-stat__label">
                Pemain Aktif
            </span>

            <strong>
                {{ number_format($playerCount, 0, ',', '.') }}
            </strong>

            <small>Lihat data pemain →</small>

        </a>

        <a href="{{ route('admin.matches.index') }}" class="a4-stat">

            <span class="a4-stat__label">
                Total Pertandingan
            </span>

            <strong>
                {{ number_format($matchCount, 0, ',', '.') }}
            </strong>

            <small>
                {{ $regularMatchCount }} Reguler
                · {{ $tournamentMatchCount }} Liga/Cup
            </small>

        </a>

        <a href="{{ route('admin.tournaments.index') }}" class="a4-stat">

            <span class="a4-stat__label">
                Total Turnamen
            </span>

            <strong>
                {{ number_format($tournamentCount, 0, ',', '.') }}
            </strong>

            <small>Lihat semua turnamen →</small>

        </a>

        <a href="{{ route('admin.cash.index') }}" class="a4-stat a4-stat--finance">

            <span class="a4-stat__label">
                Saldo Kas
            </span>

            <strong>
                Rp {{ number_format($cashBalance, 0, ',', '.') }}
            </strong>

            <small>Lihat laporan keuangan →</small>

        </a>

    </div>

    <section class="a4-panel">

        <div class="a4-panel__heading">
            <h3>Akses Cepat</h3>
            <p>Pengelolaan data PB Alfa</p>
        </div>

        <div class="a4-shortcuts">

            <a href="{{ route('admin.players.index') }}">
                Kelola Pemain <span>→</span>
            </a>

            <a href="{{ route('admin.matches.index') }}">
                Kelola Pertandingan <span>→</span>
            </a>

            <a href="{{ route('admin.tournaments.index') }}">
                Kelola Turnamen <span>→</span>
            </a>

            <a href="{{ route('admin.cash.index') }}">
                Kelola Keuangan <span>→</span>
            </a>

            <a href="{{ route('admin.gallery.index') }}">
                Galeri Klub <span>→</span>
            </a>

            <a href="{{ route('admin.settings.edit') }}">
                Pengaturan Website <span>→</span>
            </a>

        </div>

    </section>

</div>

@endsection