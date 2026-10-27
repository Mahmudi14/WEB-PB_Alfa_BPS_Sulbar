@extends('layouts.app')

@section('title', 'Turnamen - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=5">
@endpush

@section('content')

<div class="tr-page">

    <header class="tr-hero">
        <div class="tr-hero__main">

            <span class="tr-eyebrow">
                PB ALFA / TOURNAMENT CENTER
            </span>

            <h1>TURNAMEN <span>PB ALFA.</span></h1>

            <p>
                Kompetisi, jadwal ronde, hasil pertandingan,
                dan klasemen PB Alfa BPS Sulawesi Barat.
            </p>
        </div>

        @if($canManage)
        <div class="tr-hero__actions">
            <a class="tr-btn tr-btn--accent" href="{{ route('tournaments.create') }}">
                + Buat Turnamen
            </a>
        </div>
        @else
        <span class="tr-hero__decoration" aria-hidden="true">🏆</span>
        @endif
    </header>

    <section class="tr-board" aria-labelledby="tr-list-title">

        <div class="tr-board-head">
            <div>
                <span class="tr-eyebrow">
                    OFFICIAL TOURNAMENTS
                </span>
                <h2 id="tr-list-title">
                    Daftar Turnamen
                </h2>
            </div>

            <span class="tr-board-count">
                {{ $tournaments->total() }} turnamen
            </span>
        </div>

        @if($tournaments->isEmpty())
        <div class="tr-empty">
            <span class="tr-empty__icon" aria-hidden="true">🏆</span>
            <h2>Belum ada turnamen</h2>
            <p>
                Turnamen yang dibuat oleh admin atau
                pengelola akan muncul di sini.
            </p>
        </div>
        @else

        <div class="tr-list">

            @foreach($tournaments as $tournament)

            @php
            $isDraft = $tournament->status === 'draft';

            $detailUrl = $isDraft
            ? route('tournaments.entries', $tournament)
            : route('tournaments.show', $tournament);

            $statusText = match ($tournament->status) {
            'draft' => 'Draft',
            'active' => 'Berlangsung',
            'completed' => 'Selesai',
            default => 'Tidak diketahui'
            };
            @endphp

            <div class="tr-list-row">

                <a href="{{ $detailUrl }}" class="tr-list-card">

                    <span class="tr-list-icon" aria-hidden="true">
                        {{ $tournament->format === 'cup'
                        ? '🏆'
                        : '🏸' }}
                    </span>

                    <div class="tr-list-body">

                        <div class="tr-mini">
                            <span>
                                {{ $tournament->format === 'cup'
                                ? 'CUP / SISTEM GUGUR'
                                : 'LIGA / ROUND ROBIN' }}
                            </span>

                            <span class="tr-badge tr-badge--{{ $tournament->status }}">
                                {{ $statusText }}
                            </span>
                        </div>

                        <h2>{{ $tournament->name }}</h2>

                        <p>
                            {{ ucfirst($tournament->match_type) }}
                            · {{ $tournament->entries_count }} peserta
                            · {{ $tournament->set_format_label }}
                            · {{ $tournament->target_points }} poin
                        </p>

                        @if($tournament->format === 'liga')
                        <p>
                            {{ $tournament->league_legs ?? 1 }}
                            kali pertemuan
                        </p>
                        @endif

                    </div>

                    <span class="tr-card-arrow" aria-hidden="true">›</span>

                </a>

                @if($canManage)

                <form method="POST" class="tr-delete-form" action="{{ route('tournaments.destroy', $tournament) }}"
                    data-single-submit
                    data-confirm="Hapus turnamen {{ $tournament->name }}? Semua peserta, jadwal dan skor turnamen ini akan dihapus permanen."
                    data-confirm-title="Hapus Turnamen" data-confirm-text="Ya, Hapus">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="tr-delete-btn" aria-label="Hapus turnamen {{ $tournament->name }}"
                        title="Hapus Turnamen">

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 6h18" />
                            <path d="M8 6V4h8v2" />
                            <path d="M5 6l1 15h12l1-15" />
                            <path d="M10 10v7M14 10v7" />
                        </svg>

                        <span>Hapus</span>
                    </button>

                </form>

                @endif

            </div>

            @endforeach

        </div>

        <div class="tr-pagination">
            {{ $tournaments->links() }}
        </div>

        @endif

    </section>

</div>

@endsection