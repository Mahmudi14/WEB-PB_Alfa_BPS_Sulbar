@extends('layouts.admin')

@section('title', 'Dashboard Admin - PB Alfa')

@section('page_title', 'Dashboard')

@section('content')

@php

$balanceLabel = $cashBalance < 0 ? '-Rp ' . number_format(abs($cashBalance), 0, ',' , '.' ) : 'Rp ' .
    number_format($cashBalance, 0, ',' , '.' ); $statCards=[ [ 'label'=> 'Pemain Aktif',
    'value' => number_format($playerCount, 0, ',', '.'),
    'description' => 'Pemain yang masih terdaftar aktif',
    'route' => 'admin.players.index',
    'icon' => 'users',
    'theme' => 'players',
    ],
    [
    'label' => 'Total Pertandingan',
    'value' => number_format($matchCount, 0, ',', '.'),
    'description' => 'Reguler, Liga, dan Cup',
    'route' => 'admin.matches.index',
    'icon' => 'match',
    'theme' => 'matches',
    ],
    [
    'label' => 'Total Turnamen',
    'value' => number_format($tournamentCount, 0, ',', '.'),
    'description' => 'Seluruh Liga dan Cup',
    'route' => 'admin.tournaments.index',
    'icon' => 'trophy',
    'theme' => 'tournaments',
    ],
    [
    'label' => 'Saldo Kas',
    'value' => $balanceLabel,
    'description' => 'Pemasukan dikurangi pengeluaran',
    'route' => 'admin.cash.index',
    'icon' => 'wallet',
    'theme' => 'cash',
    ],
    ];

    $quickMenus = [
    [
    'title' => 'Data Pemain',
    'description' => 'Profil, role, status, dan ranking',
    'route' => 'admin.players.index',
    'icon' => 'users',
    ],
    [
    'title' => 'Pertandingan',
    'description' => 'Riwayat, skor, dan kok',
    'route' => 'admin.matches.index',
    'icon' => 'match',
    ],
    [
    'title' => 'Turnamen',
    'description' => 'Liga, Cup, dan jadwal',
    'route' => 'admin.tournaments.index',
    'icon' => 'trophy',
    ],
    [
    'title' => 'Keuangan',
    'description' => 'Saldo dan transaksi',
    'route' => 'admin.cash.index',
    'icon' => 'wallet',
    ],
    [
    'title' => 'Galeri Klub',
    'description' => 'Kelola dokumentasi klub',
    'route' => 'admin.gallery.index',
    'icon' => 'image',
    ],
    [
    'title' => 'Pengaturan Website',
    'description' => 'Logo dan informasi website',
    'route' => 'admin.settings.edit',
    'icon' => 'settings',
    ],
    ];

    @endphp


    <div class="adm-dashboard">

        {{-- HEADER --}}

        <div class="adm-dashboard__heading">

            <div>

                <span class="adm-eyebrow">
                    OVERVIEW / PB ALFA
                </span>

                <h2>
                    Ringkasan Klub
                </h2>

                <p>
                    Pantau informasi utama PB Alfa
                    BPS Sulawesi Barat melalui satu dashboard.
                </p>

            </div>

            <span class="adm-dashboard__date">
                {{ now()->locale('id')->translatedFormat('d F Y') }}
            </span>

        </div>


        {{-- EMPAT STATISTIK UTAMA --}}

        <section class="adm-dashboard__stats" aria-label="Statistik utama klub">

            @foreach($statCards as $card)

            @php
            $available =
            \Illuminate\Support\Facades\Route::has(
            $card['route']
            );
            @endphp

            @if($available)

            <a href="{{ route($card['route']) }}"
                class="adm-dash-stat adm-dash-stat--{{ $card['theme'] }} adm-dash-stat--linked">

                @else

                <article class="adm-dash-stat adm-dash-stat--{{ $card['theme'] }}">

                    @endif

                    <div class="adm-dash-stat__top">

                        <span class="adm-dash-stat__icon">

                            @include(
                            'admin.partials.nav-icon',
                            ['name' => $card['icon']]
                            )

                        </span>

                        @if($available)
                        <span class="adm-dash-stat__arrow" aria-hidden="true">
                            &rarr;
                        </span>
                        @endif

                    </div>

                    <span class="adm-dash-stat__label">
                        {{ $card['label'] }}
                    </span>

                    <strong class="adm-dash-stat__value">
                        {{ $card['value'] }}
                    </strong>

                    <span class="adm-dash-stat__description">
                        {{ $card['description'] }}
                    </span>

                    @if($available)

            </a>

            @else

            </article>

            @endif

            @endforeach

        </section>


        {{-- RINGKASAN OPERASIONAL --}}

        <div class="adm-dashboard__details">

            {{-- PERTANDINGAN --}}

            <section class="adm-card adm-dash-panel">

                <div class="adm-dash-panel__header">

                    <div>

                        <span class="adm-eyebrow">
                            MATCH OVERVIEW
                        </span>

                        <h3>
                            Ringkasan Pertandingan
                        </h3>

                        <p>
                            Jumlah pertandingan berdasarkan sumber
                            dan kelengkapan hasil.
                        </p>

                    </div>

                    <span class="adm-dash-panel__icon">
                        @include(
                        'admin.partials.nav-icon',
                        ['name' => 'match']
                        )
                    </span>

                </div>

                <div class="adm-dash-list">

                    <div class="adm-dash-list__row">

                        <span>
                            Pertandingan Reguler
                        </span>

                        <strong>
                            {{ number_format(
                            $regularMatchCount, 0, ',', '.'
                            ) }}
                        </strong>

                    </div>

                    <div class="adm-dash-list__row">

                        <span>
                            Reguler Selesai
                        </span>

                        <strong class="adm-dash-text--success">
                            {{ number_format(
                            $completedRegularCount, 0, ',', '.'
                            ) }}
                        </strong>

                    </div>

                    <div class="adm-dash-list__row">

                        <span>
                            Reguler Belum Selesai / Tidak Lengkap
                        </span>

                        <strong class="adm-dash-text--warning">
                            {{ number_format(
                            $pendingRegularCount, 0, ',', '.'
                            ) }}
                        </strong>

                    </div>

                    <div class="adm-dash-list__row">

                        <span>
                            Liga dan Cup Selesai
                        </span>

                        <strong>
                            {{ number_format(
                            $tournamentMatchCount, 0, ',', '.'
                            ) }}
                        </strong>

                    </div>

                    <div class="adm-dash-list__row adm-dash-list__row--total">

                        <span>
                            Total Pertandingan
                        </span>

                        <strong>
                            {{ number_format(
                            $matchCount, 0, ',', '.'
                            ) }}
                        </strong>

                    </div>

                </div>

            </section>


            {{-- KEUANGAN --}}

            <section class="adm-card adm-dash-panel">

                <div class="adm-dash-panel__header">

                    <div>

                        <span class="adm-eyebrow">
                            FINANCIAL OVERVIEW
                        </span>

                        <h3>
                            Ringkasan Keuangan
                        </h3>

                        <p>
                            Akumulasi seluruh transaksi kas
                            yang masih aktif.
                        </p>

                    </div>

                    <span class="adm-dash-panel__icon">
                        @include(
                        'admin.partials.nav-icon',
                        ['name' => 'wallet']
                        )
                    </span>

                </div>

                <div class="adm-dash-finance">

                    <div class="adm-dash-finance__item">

                        <span class="adm-dash-finance__label">
                            Total Pemasukan
                        </span>

                        <strong class="adm-dash-text--success">
                            Rp {{ number_format(
                            $income, 0, ',', '.'
                            ) }}
                        </strong>

                    </div>

                    <div class="adm-dash-finance__item">

                        <span class="adm-dash-finance__label">
                            Total Pengeluaran
                        </span>

                        <strong class="adm-dash-text--danger">
                            Rp {{ number_format(
                            $expense, 0, ',', '.'
                            ) }}
                        </strong>

                    </div>

                    <div class="adm-dash-finance__balance">

                        <span>
                            Saldo Kas Saat Ini
                        </span>

                        <strong>
                            {{ $balanceLabel }}
                        </strong>

                    </div>

                </div>

            </section>

        </div>


        {{-- STATUS TURNAMEN --}}

        <section class="adm-card adm-dash-tournaments">

            <div class="adm-dash-panel__header">

                <div>

                    <span class="adm-eyebrow">
                        TOURNAMENT OVERVIEW
                    </span>

                    <h3>
                        Status Turnamen
                    </h3>

                    <p>
                        Gambaran seluruh turnamen yang tersimpan.
                    </p>

                </div>

                <span class="adm-dash-panel__icon">
                    @include(
                    'admin.partials.nav-icon',
                    ['name' => 'trophy']
                    )
                </span>

            </div>

            <div class="adm-dash-tournaments__grid">

                <div class="adm-dash-tournament-status">

                    <span class="adm-pill adm-pill--warning">
                        Draft
                    </span>

                    <strong>
                        {{ number_format(
                        $tournamentDraftCount, 0, ',', '.'
                        ) }}
                    </strong>

                    <small>Belum dimulai</small>

                </div>

                <div class="adm-dash-tournament-status">

                    <span class="adm-pill adm-pill--info">
                        Aktif
                    </span>

                    <strong>
                        {{ number_format(
                        $tournamentActiveCount, 0, ',', '.'
                        ) }}
                    </strong>

                    <small>Sedang berlangsung</small>

                </div>

                <div class="adm-dash-tournament-status">

                    <span class="adm-pill adm-pill--success">
                        Selesai
                    </span>

                    <strong>
                        {{ number_format(
                        $tournamentCompletedCount, 0, ',', '.'
                        ) }}
                    </strong>

                    <small>Telah diselesaikan</small>

                </div>

            </div>

        </section>


        {{-- AKSES CEPAT --}}

        <section class="adm-card adm-dash-shortcuts">

            <div class="adm-dash-panel__header">

                <div>

                    <span class="adm-eyebrow">
                        QUICK ACCESS
                    </span>

                    <h3>
                        Akses Cepat
                    </h3>

                    <p>
                        Buka modul untuk mengelola
                        aktivitas klub.
                    </p>

                </div>

            </div>

            <div class="adm-dash-shortcuts__grid">

                @foreach($quickMenus as $menu)

                @php
                $available =
                \Illuminate\Support\Facades\Route::has(
                $menu['route']
                );
                @endphp

                @if($available)

                <a href="{{ route($menu['route']) }}" class="adm-dash-shortcut">

                    @else

                    <div class="adm-dash-shortcut adm-dash-shortcut--disabled">

                        @endif

                        <span class="adm-dash-shortcut__icon">

                            @include(
                            'admin.partials.nav-icon',
                            ['name' => $menu['icon']]
                            )

                        </span>

                        <span class="adm-dash-shortcut__text">

                            <strong>
                                {{ $menu['title'] }}
                            </strong>

                            <small>
                                {{ $menu['description'] }}
                            </small>

                        </span>

                        @if($available)

                        <span class="adm-dash-shortcut__arrow" aria-hidden="true">
                            &rarr;
                        </span>

                        @else

                        <span class="adm-dash-shortcut__soon">
                            Segera
                        </span>

                        @endif

                        @if($available)

                </a>

                @else

            </div>

            @endif

            @endforeach

    </div>

    </section>

    </div>

    @endsection