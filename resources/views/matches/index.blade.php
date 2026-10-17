@extends('layouts.app')

@section('title', 'Pertandingan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=6">
@endpush

@section('content')

@php
    $viewer = auth()->user();
    $canManage = $viewer?->canManage() ?? false;
    $canCreate = $viewer && in_array(
        $viewer->role,
        ['admin', 'pengelola', 'pemain'],
        true
    );
    $lastDate = null;
@endphp

<div class="match-page">

    <header class="match-hero">
        <div>
            <span class="match-eyebrow">PB ALFA / MATCH HISTORY</span>
            <h1>RIWAYAT <span>PERTANDINGAN.</span></h1>
            <p>Catatan pertandingan PB Alfa BPS Sulawesi Barat.</p>
        </div>

        <span class="match-hero__decoration" aria-hidden="true">
            VS
        </span>
    </header>

    {{-- Filter --}}

    <div class="match-toolbar">

        <form
            method="GET"
            action="{{ route('matches.index') }}"
            class="match-filter"
        >
            <label for="match-date">Filter berdasarkan tanggal</label>

            <div class="match-filter__controls">

                <input
                    id="match-date"
                    type="date"
                    name="tanggal"
                    value="{{ $tanggal ?? '' }}"
                >

                @if($tanggal)
                    <a
                        href="{{ route('matches.index') }}"
                        class="match-filter__reset"
                    >
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

    {{-- Daftar --}}

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
                    $dateKey = $match->played_on->toDateString();

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

                    $isDraw = $match->result === 'seri';
                    $winnerA = $match->result === 'a';
                    $winnerB = $match->result === 'b';

                    $nameClassA = mb_strlen($teamA) > 28
                        ? 'match-team--very-long'
                        : (mb_strlen($teamA) > 17 ? 'match-team--long' : '');

                    $nameClassB = mb_strlen($teamB) > 28
                        ? 'match-team--very-long'
                        : (mb_strlen($teamB) > 17 ? 'match-team--long' : '');

                    $set1 = $match->set1_a !== null
                        && $match->set1_b !== null
                        ? $match->set1_a . ' : ' . $match->set1_b
                        : '— : —';

                    $set2 = $match->set2_a !== null
                        && $match->set2_b !== null
                        ? $match->set2_a . ' : ' . $match->set2_b
                        : '— : —';
                @endphp

                {{-- Tanggal --}}

                @if($lastDate !== $dateKey)
                    <h3 class="match-divider">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M7 3v4M17 3v4M3 10h18"/>
                        </svg>

                        <span>
                            {{ $match->played_on
                                ->locale('id')
                                ->translatedFormat('l, d F Y') }}
                        </span>

                    </h3>

                    @php
                        $lastDate = $dateKey;
                    @endphp
                @endif

                {{-- Pertandingan --}}

                <article class="match-row {{ $canManage ? 'match-row--manage' : '' }}">

                    <div class="match-row__teams">

                        {{-- Tim A dan Set 1 --}}

                        <div class="match-side match-side--a">

                            <div class="match-team
                                {{ $isDraw ? 'is-draw' : ($winnerA ? 'is-winner' : 'is-loser') }}
                                {{ $nameClassA }}">

                                <span class="match-team__label">
                                    TIM A
                                </span>

                                <strong>{{ $teamA }}</strong>

                                <span class="match-sr-only">
                                    {{ $isDraw ? 'Seri' : ($winnerA ? 'Menang' : 'Kalah') }}
                                </span>

                            </div>

                            <div class="match-set">
                                <small>SET 1</small>
                                <strong>{{ $set1 }}</strong>
                            </div>

                        </div>

                        {{-- VS dan Kok --}}

                        <div class="match-center">

                            <span class="match-row__vs">
                                VS
                            </span>

                            <div
                                class="match-cock"
                                aria-label="Jumlah kok {{ $match->shuttlecock_count }}"
                            >
                                <small>KOK</small>
                                <strong>{{ $match->shuttlecock_count }}</strong>
                            </div>

                        </div>

                        {{-- Tim B dan Set 2 --}}

                        <div class="match-side match-side--b">

                            <div class="match-team
                                {{ $isDraw ? 'is-draw' : ($winnerB ? 'is-winner' : 'is-loser') }}
                                {{ $nameClassB }}">

                                <span class="match-team__label">
                                    TIM B
                                </span>

                                <strong>{{ $teamB }}</strong>

                                <span class="match-sr-only">
                                    {{ $isDraw ? 'Seri' : ($winnerB ? 'Menang' : 'Kalah') }}
                                </span>

                            </div>

                            <div class="match-set">
                                <small>SET 2</small>
                                <strong>{{ $set2 }}</strong>
                            </div>

                        </div>

                    </div>

                    {{-- Hapus --}}

                    @if($canManage)
                        <form
                            method="POST"
                            action="{{ route('matches.destroy', $match) }}"
                            class="match-delete-form"
                            data-single-submit
                            data-confirm="Hapus pertandingan ini?"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="match-delete"
                                title="Hapus pertandingan"
                                aria-label="Hapus pertandingan {{ $teamA }} melawan {{ $teamB }}"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="M5 6l1 15h12l1-15"/>
                                    <path d="M10 10v7M14 10v7"/>
                                </svg>

                                <span>Hapus</span>
                            </button>
                        </form>
                    @endif

                </article>

            @empty

                <div class="match-empty">

                    @if($tanggal)
                        <strong>
                            Tidak ada pertandingan pada tanggal ini.
                        </strong>

                        <a href="{{ route('matches.index') }}">
                            Lihat Semua Pertandingan →
                        </a>
                    @else
                        <strong>Belum ada pertandingan.</strong>
                    @endif

                </div>

            @endforelse

        </div>

        {{-- Pagination --}}

        @if($matches->hasPages())

            <nav
                class="match-pagination"
                aria-label="Navigasi halaman pertandingan"
            >

                @if($matches->onFirstPage())
                    <span class="is-disabled">← Sebelumnya</span>
                @else
                    <a href="{{ $matches->previousPageUrl() }}">
                        ← Sebelumnya
                    </a>
                @endif

                <span class="match-pagination__info">
                    {{ $matches->currentPage() }}
                    / {{ $matches->lastPage() }}
                </span>

                @if($matches->hasMorePages())
                    <a href="{{ $matches->nextPageUrl() }}">
                        Berikutnya →
                    </a>
                @else
                    <span class="is-disabled">Berikutnya →</span>
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

    if (input) {
        input.addEventListener('change', function () {
            input.form.requestSubmit();
        });
    }
});
</script>
@endpush