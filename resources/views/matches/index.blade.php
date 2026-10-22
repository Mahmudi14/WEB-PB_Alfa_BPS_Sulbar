@extends('layouts.app')

@section('title', 'Pertandingan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=7">
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

    {{-- Header --}}
    <header class="match-hero">
        <div>
            <span class="match-eyebrow">
                PB ALFA / MATCH HISTORY
            </span>

            <h1>
                RIWAYAT <span>PERTANDINGAN.</span>
            </h1>

            <p>
                Catatan pertandingan PB Alfa BPS Sulawesi Barat.
            </p>
        </div>

        <span class="match-hero__decoration" aria-hidden="true">
            VS
        </span>
    </header>

    {{-- Filter dan Tambah --}}
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

    {{-- Daftar Pertandingan --}}
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

            // Nama pemain
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

            // Kelengkapan skor
            $hasScores = $match->set1_a !== null
            && $match->set1_b !== null
            && $match->set2_a !== null
            && $match->set2_b !== null;

            // Status pertandingan
            $isPending = !$hasScores
            || $match->result === null;

            $isDraw = !$isPending
            && $match->result === 'seri';

            $winnerA = !$isPending
            && $match->result === 'a';

            $winnerB = !$isPending
            && $match->result === 'b';

            // Warna setiap tim
            $statusA = $isPending
            ? 'is-pending'
            : ($isDraw
            ? 'is-draw'
            : ($winnerA ? 'is-winner' : 'is-loser'));

            $statusB = $isPending
            ? 'is-pending'
            : ($isDraw
            ? 'is-draw'
            : ($winnerB ? 'is-winner' : 'is-loser'));

            // Keterangan aksesibilitas
            $statusTextA = $isPending
            ? 'Belum ada hasil'
            : ($isDraw
            ? 'Seri'
            : ($winnerA ? 'Menang' : 'Kalah'));

            $statusTextB = $isPending
            ? 'Belum ada hasil'
            : ($isDraw
            ? 'Seri'
            : ($winnerB ? 'Menang' : 'Kalah'));

            // Ukuran teks pemain
            $nameClassA = mb_strlen($teamA) > 28
            ? 'match-team--very-long'
            : (mb_strlen($teamA) > 17
            ? 'match-team--long'
            : '');

            $nameClassB = mb_strlen($teamB) > 28
            ? 'match-team--very-long'
            : (mb_strlen($teamB) > 17
            ? 'match-team--long'
            : '');

            // Skor
            $set1 = $match->set1_a !== null
            && $match->set1_b !== null
            ? $match->set1_a . ' : ' . $match->set1_b
            : '— : —';

            $set2 = $match->set2_a !== null
            && $match->set2_b !== null
            ? $match->set2_a . ' : ' . $match->set2_b
            : '— : —';

            // Hak membuka halaman edit
            $canOpenEditor = $viewer
            && $viewer->is_active
            && (
            $viewer->canManage()
            || (
            $viewer->role === 'pemain'
            && $viewer->player !== null
            && (int) $match->created_by
            === (int) $viewer->id
            )
            );
            @endphp

            {{-- Pemisah tanggal --}}
            @if($lastDate !== $dateKey)

            <h3 class="match-divider">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M7 3v4M17 3v4M3 10h18" />
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

            {{-- Kartu pertandingan --}}
            <article class="match-row
                    {{ $canOpenEditor ? 'match-row--manage' : '' }}
                    {{ $isPending ? 'match-row--pending' : '' }}">

                <div class="match-row__teams">

                    {{-- Tim A --}}
                    <div class="match-side match-side--a">

                        <div class="match-team
                                {{ $statusA }}
                                {{ $nameClassA }}">

                            <span class="match-team__label">
                                TIM A
                            </span>

                            <strong>{{ $teamA }}</strong>

                            <span class="match-sr-only">
                                {{ $statusTextA }}
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

                        <div class="match-cock" aria-label="Jumlah kok {{ $match->shuttlecock_count }}">
                            <small>KOK</small>
                            <strong>
                                {{ $match->shuttlecock_count }}
                            </strong>
                        </div>

                    </div>

                    {{-- Tim B --}}
                    <div class="match-side match-side--b">

                        <div class="match-team
                                {{ $statusB }}
                                {{ $nameClassB }}">

                            <span class="match-team__label">
                                TIM B
                            </span>

                            <strong>{{ $teamB }}</strong>

                            <span class="match-sr-only">
                                {{ $statusTextB }}
                            </span>
                        </div>

                        <div class="match-set">
                            <small>SET 2</small>
                            <strong>{{ $set2 }}</strong>
                        </div>

                    </div>

                </div>

                {{-- Tombol Edit --}}
                @if($canOpenEditor)

                <a href="{{ route('matches.edit', $match) }}" class="match-edit" title="Buka halaman edit pertandingan"
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
                / {{ $matches->lastPage() }}
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

    if (input) {
        input.addEventListener('change', function () {
            input.form.requestSubmit();
        });
    }
});
</script>
@endpush