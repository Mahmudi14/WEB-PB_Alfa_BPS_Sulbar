@extends('layouts.app')

@section('title', 'Pertandingan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=8">
@endpush

@section('content')

@php
$viewer = auth()->user();

$canCreate = $viewer
&& $viewer->is_active
&& (
$viewer->canManage()
|| (
$viewer->role === 'pemain'
&& $viewer->player !== null
)
);

$lastDate = null;
@endphp

<div class="match-page">

    {{-- ==========================================
    HEADER
    ========================================== --}}

    <header class="match-hero">
        <div>
            <span class="match-eyebrow">
                PB ALFA / MATCH HISTORY
            </span>

            <h1>
                RIWAYAT <span>PERTANDINGAN.</span>
            </h1>

            <p>
                Catatan pertandingan resmi PB Alfa
                BPS Sulawesi Barat.
            </p>
        </div>

        <span class="match-hero__decoration" aria-hidden="true">
            VS
        </span>
    </header>


    {{-- ==========================================
    FILTER TANGGAL DAN TAMBAH
    ========================================== --}}

    <div class="match-toolbar">

        <form method="GET" action="{{ route('matches.index') }}" class="match-filter">
            <label for="match-date">
                Filter berdasarkan tanggal
            </label>

            <div class="match-filter__controls">

                <input id="match-date" type="date" name="tanggal" value="{{ $tanggal ?? '' }}">

                @if($tanggal)
                <a href="{{ route('matches.index') }}" class="match-filter__reset">
                    Lihat Semua
                </a>
                @endif

            </div>

            <noscript>
                <button type="submit" class="match-filter__apply">
                    Terapkan
                </button>
            </noscript>

        </form>

        @if($canCreate)
        <a href="{{ route('matches.create') }}" class="match-add">
            <span aria-hidden="true">+</span>
            Tambah Pertandingan
        </a>
        @endif

    </div>


    {{-- ==========================================
    RIWAYAT PERTANDINGAN RESMI
    REGULER + LIGA + CUP
    ========================================== --}}

    <section class="match-board">

        <div class="match-board__heading">

            <div>
                <span class="match-eyebrow match-eyebrow--green">
                    OFFICIAL MATCH RECORDS
                </span>

                <h2>Daftar Pertandingan</h2>
            </div>

            <span class="match-board__count">
                {{ $matches->total() }} pertandingan
            </span>

        </div>


        <div class="match-list">

            @forelse($matches as $match)

            @php

            /*
            |--------------------------------------------------------------------------
            | IDENTITAS SUMBER
            |--------------------------------------------------------------------------
            |
            | Reguler: tidak menampilkan label.
            | Liga/Cup: menampilkan nama turnamen.
            |
            */

            $isTournament = in_array(
            $match->history_source ?? null,
            ['liga', 'cup'],
            true
            );

            $dateKey = $match->played_on
            ->toDateString();


            /*
            |--------------------------------------------------------------------------
            | NAMA PEMAIN TIM A DAN TIM B
            |--------------------------------------------------------------------------
            */

            $teamA = $match->participants
            ->where('team', 'a')
            ->sortBy('slot')
            ->map(fn ($item) => $item->display_name)
            ->implode(' / ');

            $teamB = $match->participants
            ->where('team', 'b')
            ->sortBy('slot')
            ->map(fn ($item) => $item->display_name)
            ->implode(' / ');


            /*
            |--------------------------------------------------------------------------
            | VALIDASI HASIL
            |--------------------------------------------------------------------------
            */

            $hasScores =
            $match->set1_a !== null
            && $match->set1_b !== null
            && $match->set2_a !== null
            && $match->set2_b !== null;

            $isPending = !$hasScores
            || $match->result === null;

            $isDraw = !$isPending
            && $match->result === 'seri';

            $winnerA = !$isPending
            && $match->result === 'a';

            $winnerB = !$isPending
            && $match->result === 'b';


            /*
            |--------------------------------------------------------------------------
            | WARNA HASIL
            |--------------------------------------------------------------------------
            |
            | Menang = Hijau
            | Seri = Abu-abu
            | Kalah = Merah
            | Pending = Oranye
            |
            */

            $statusA = $isPending
            ? 'is-pending'
            : (
            $isDraw
            ? 'is-draw'
            : (
            $winnerA
            ? 'is-winner'
            : 'is-loser'
            )
            );

            $statusB = $isPending
            ? 'is-pending'
            : (
            $isDraw
            ? 'is-draw'
            : (
            $winnerB
            ? 'is-winner'
            : 'is-loser'
            )
            );


            /*
            |--------------------------------------------------------------------------
            | STATUS AKSESIBILITAS
            |--------------------------------------------------------------------------
            */

            $statusTextA = $isPending
            ? 'Belum ada hasil'
            : (
            $isDraw
            ? 'Seri'
            : ($winnerA ? 'Menang' : 'Kalah')
            );

            $statusTextB = $isPending
            ? 'Belum ada hasil'
            : (
            $isDraw
            ? 'Seri'
            : ($winnerB ? 'Menang' : 'Kalah')
            );


            /*
            |--------------------------------------------------------------------------
            | UKURAN NAMA PEMAIN
            |--------------------------------------------------------------------------
            */

            $nameClassA = mb_strlen($teamA) > 28
            ? 'match-team--very-long'
            : (
            mb_strlen($teamA) > 17
            ? 'match-team--long'
            : ''
            );

            $nameClassB = mb_strlen($teamB) > 28
            ? 'match-team--very-long'
            : (
            mb_strlen($teamB) > 17
            ? 'match-team--long'
            : ''
            );


            /*
            |--------------------------------------------------------------------------
            | SKOR SET 1 DAN SET 2
            |--------------------------------------------------------------------------
            */

            $set1 = (
            $match->set1_a !== null
            && $match->set1_b !== null
            )
            ? $match->set1_a
            . ' : '
            . $match->set1_b
            : '— : —';

            $set2 = (
            $match->set2_a !== null
            && $match->set2_b !== null
            )
            ? $match->set2_a
            . ' : '
            . $match->set2_b
            : '— : —';


            /*
            |--------------------------------------------------------------------------
            | HAK EDIT
            |--------------------------------------------------------------------------
            |
            | Liga/Cup:
            | - Admin dan Pengelola
            |
            | Reguler:
            | - Admin dan Pengelola
            | - Pemain pembuat pertandingan
            |
            | Batas edit Reguler tetap divalidasi
            | oleh controller/model.
            |
            */

            $canOpenEditor = $viewer
            && $viewer->is_active
            && (
            $isTournament
            ? $viewer->canManage()
            : (
            $viewer->canManage()
            || (
            $viewer->role === 'pemain'
            && $viewer->player !== null
            && (int) $match->created_by
            === (int) $viewer->id
            )
            )
            );


            /*
            |--------------------------------------------------------------------------
            | URL EDIT SESUAI MODUL ASAL
            |--------------------------------------------------------------------------
            */

            $editUrl = $isTournament
            ? $match->edit_url
            : route('matches.edit', $match);

            @endphp


            {{-- ==========================================
            PEMISAH BERDASARKAN TANGGAL
            ========================================== --}}

            @if($lastDate !== $dateKey)

            <h3 class="match-divider">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="2" />

                    <path d="M7 3v4M17 3v4M3 10h18" />
                </svg>

                <span>
                    {{
                    $match->played_on
                    ->locale('id')
                    ->translatedFormat('l, d F Y')
                    }}
                </span>

            </h3>

            @php
            $lastDate = $dateKey;
            @endphp

            @endif


            {{-- ==========================================
            KARTU PERTANDINGAN
            ========================================== --}}

            <article class="match-row
                        {{ $canOpenEditor ? 'match-row--manage' : '' }}
                        {{ $isPending ? 'match-row--pending' : '' }}">

                {{-- ======================================
                LABEL LIGA / CUP
                ====================================== --}}

                @if($isTournament)

                <div class="match-tournament-info">

                    <a href="{{ $match->tournament_url }}" class="match-tournament-badge
                                    match-tournament-badge--{{ $match->history_source }}">
                        {{ strtoupper($match->history_source) }}
                    </a>

                    <strong class="match-tournament-name">
                        {{ $match->tournament_name }}
                    </strong>

                    <span class="match-tournament-round">
                        Ronde {{ $match->round_number }}
                    </span>

                </div>

                @endif


                {{-- ======================================
                AREA KEDUA TIM
                ====================================== --}}

                <div class="match-row__teams">


                    {{-- ==================================
                    TIM A
                    ================================== --}}

                    <div class="match-side match-side--a">

                        <div class="match-team
                                    {{ $statusA }}
                                    {{ $nameClassA }}">

                            <span class="match-team__label">
                                TIM A
                            </span>

                            <strong>
                                {{ $teamA }}
                            </strong>

                            <span class="match-sr-only">
                                {{ $statusTextA }}
                            </span>

                        </div>


                        <div class="match-set">

                            <small>SET 1</small>

                            <strong>
                                {{ $set1 }}
                            </strong>

                        </div>

                    </div>


                    {{-- ==================================
                    VS DAN JUMLAH KOK
                    ================================== --}}

                    <div class="match-center">

                        <span class="match-row__vs">
                            VS
                        </span>

                        <div class="match-cock" aria-label="Jumlah kok {{ $match->shuttlecock_count }}">

                            <small>KOK</small>

                            <strong>
                                {{ $match->shuttlecock_count }}
                            </strong>

                        </div>

                    </div>


                    {{-- ==================================
                    TIM B
                    ================================== --}}

                    <div class="match-side match-side--b">

                        <div class="match-team
                                    {{ $statusB }}
                                    {{ $nameClassB }}">

                            <span class="match-team__label">
                                TIM B
                            </span>

                            <strong>
                                {{ $teamB }}
                            </strong>

                            <span class="match-sr-only">
                                {{ $statusTextB }}
                            </span>

                        </div>


                        <div class="match-set">

                            <small>SET 2</small>

                            <strong>
                                {{ $set2 }}
                            </strong>

                        </div>

                    </div>


                    {{-- ==================================
                    SET KETIGA / POIN PENENTU
                    KHUSUS LIGA DAN CUP
                    ================================== --}}

                    @if($isTournament)

                    @if(
                    $match->set3_a !== null
                    && $match->set3_b !== null
                    )

                    <div class="match-tournament-extra">

                        <span>SET 3</span>

                        <strong>
                            {{ $match->set3_a }}
                            :
                            {{ $match->set3_b }}
                        </strong>

                    </div>

                    @endif


                    @if(
                    $match->jus_a !== null
                    && $match->jus_b !== null
                    )

                    <div class="match-tournament-extra">

                        <span>POIN PENENTU</span>

                        <strong>
                            {{ $match->jus_a }}
                            :
                            {{ $match->jus_b }}
                        </strong>

                    </div>

                    @endif

                    @endif

                </div>


                {{-- ======================================
                TOMBOL EDIT
                ====================================== --}}

                @if($canOpenEditor)

                <a href="{{ $editUrl }}" class="match-edit" title="Buka halaman edit pertandingan"
                    aria-label="Edit pertandingan {{ $teamA }} melawan {{ $teamB }}">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9" />

                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z" />
                    </svg>

                    <span>Edit</span>

                </a>

                @endif

            </article>


            {{-- ==========================================
            BELUM ADA RIWAYAT
            ========================================== --}}

            @empty

            <div class="match-empty">

                @if($tanggal)

                <strong>
                    Tidak ada pertandingan selesai
                    pada tanggal ini.
                </strong>

                <a href="{{ route('matches.index') }}">
                    Lihat Semua Pertandingan →
                </a>

                @else

                <strong>
                    Belum ada pertandingan selesai.
                </strong>

                @endif

            </div>

            @endforelse

        </div>


        {{-- ==========================================
        PAGINATION
        ========================================== --}}

        @if($matches->hasPages())

        <nav class="match-pagination" aria-label="Navigasi halaman pertandingan">

            @if($matches->onFirstPage())

            <span class="is-disabled">
                ← Sebelumnya
            </span>

            @else

            <a href="{{ $matches->previousPageUrl() }}">
                ← Sebelumnya
            </a>

            @endif


            <span class="match-pagination__info">
                {{ $matches->currentPage() }}
                /
                {{ $matches->lastPage() }}
            </span>


            @if($matches->hasMorePages())

            <a href="{{ $matches->nextPageUrl() }}">
                Berikutnya →
            </a>

            @else

            <span class="is-disabled">
                Berikutnya →
            </span>

            @endif

        </nav>

        @endif

    </section>

</div>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('match-date');

    if (!input) {
        return;
    }

    input.addEventListener('change', function () {
        input.form.requestSubmit();
    });
});
</script>
@endpush