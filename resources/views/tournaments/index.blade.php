@extends('layouts.app')

@section('title', 'Turnamen - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=21">
@endpush

@section('content')

<div class="tr-page">

    {{-- HEADER --}}
    <header class="tr-hero">

        <div class="tr-hero__main">

            <span class="tr-eyebrow">
                PB ALFA / TOURNAMENT CENTER
            </span>

            <h1>
                TURNAMEN <span>PB ALFA.</span>
            </h1>

        </div>

        @if($canManage)
        <div class="tr-hero__actions">

            <a href="{{ route('tournaments.create') }}" class="tr-btn tr-btn--accent">
                + Buat Turnamen
            </a>

        </div>
        @endif

    </header>


    {{-- DAFTAR TURNAMEN --}}
    <section class="tr-board" aria-labelledby="tr-index-title">

        <div class="tr-board-head">

            <div>
                <span class="tr-eyebrow">
                    OFFICIAL TOURNAMENTS
                </span>

                <h2 id="tr-index-title">
                    Daftar Turnamen
                </h2>
            </div>

            <span class="tr-board-count">
                {{ $tournaments->total() }} Turnamen
            </span>

        </div>


        {{-- =====================================
        FILTER PENCARIAN
        ===================================== --}}
        <form action="{{ route('tournaments.index') }}" method="GET" class="tr-index-filters" role="search">

            {{-- CARI NAMA --}}
            <div class="tr-index-filters__search">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    aria-hidden="true">

                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-4-4" />

                </svg>

                <input type="search" name="cari" value="{{ $cari }}" placeholder="Cari nama turnamen..."
                    aria-label="Cari nama turnamen" maxlength="100" autocomplete="off">

            </div>



            {{-- FILTER FORMAT DAN STATUS --}}
            <div class="tr-index-filters__selects">

                {{-- FILTER FORMAT --}}
                <div class="tr-index-picker" data-index-picker>

                    <input type="hidden" name="format" value="{{ $filterFormat }}" data-index-value>

                    <button type="button" class="tr-index-picker__trigger" data-index-trigger aria-haspopup="listbox"
                        aria-expanded="false" aria-controls="tr-format-options">

                        <span data-index-label>
                            {{ match ($filterFormat) {
                            'liga' => 'Liga',
                            'cup' => 'Cup',
                            default => 'Semua Format',
                            } }}
                        </span>

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>

                    </button>

                    <div id="tr-format-options" class="tr-index-picker__menu" data-index-menu role="listbox"
                        aria-label="Format turnamen" hidden>

                        <button type="button" role="option" data-index-option data-value=""
                            aria-selected="{{ $filterFormat === '' ? 'true' : 'false' }}">
                            Semua Format
                        </button>

                        <button type="button" role="option" data-index-option data-value="liga"
                            aria-selected="{{ $filterFormat === 'liga' ? 'true' : 'false' }}">
                            Liga
                        </button>

                        <button type="button" role="option" data-index-option data-value="cup"
                            aria-selected="{{ $filterFormat === 'cup' ? 'true' : 'false' }}">
                            Cup
                        </button>

                    </div>

                </div>


                {{-- FILTER STATUS --}}
                <div class="tr-index-picker" data-index-picker>

                    <input type="hidden" name="status" value="{{ $filterStatus }}" data-index-value>

                    <button type="button" class="tr-index-picker__trigger" data-index-trigger aria-haspopup="listbox"
                        aria-expanded="false" aria-controls="tr-status-options">

                        <span data-index-label>
                            {{ match ($filterStatus) {
                            'active' => 'Berlangsung',
                            'completed' => 'Selesai',
                            'draft' => 'Belum Dimulai',
                            default => 'Semua Status',
                            } }}
                        </span>

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>

                    </button>

                    <div id="tr-status-options" class="tr-index-picker__menu" data-index-menu role="listbox"
                        aria-label="Status turnamen" hidden>

                        <button type="button" role="option" data-index-option data-value=""
                            aria-selected="{{ $filterStatus === '' ? 'true' : 'false' }}">
                            Semua Status
                        </button>

                        <button type="button" role="option" data-index-option data-value="active"
                            aria-selected="{{ $filterStatus === 'active' ? 'true' : 'false' }}">
                            Berlangsung
                        </button>

                        <button type="button" role="option" data-index-option data-value="completed"
                            aria-selected="{{ $filterStatus === 'completed' ? 'true' : 'false' }}">
                            Selesai
                        </button>

                        @if($canManage)
                        <button type="button" role="option" data-index-option data-value="draft"
                            aria-selected="{{ $filterStatus === 'draft' ? 'true' : 'false' }}">
                            Belum Dimulai
                        </button>
                        @endif

                    </div>

                </div>

            </div>



            {{-- TOMBOL FILTER --}}
            <div class="tr-index-filters__actions">

                <button type="submit" class="tr-btn tr-index-filter-button">
                    Cari
                </button>

                <a href="{{ route('tournaments.index') }}" class="tr-index-filter-reset" title="Reset pencarian">
                    Reset
                </a>

            </div>

        </form>


        {{-- =====================================
        HASIL PENCARIAN
        ===================================== --}}
        @if($tournaments->isEmpty())

        <div class="tr-empty">

            <span class="tr-empty__icon" aria-hidden="true">
                🔎
            </span>

            @if($cari !== '' || $filterFormat !== '' || $filterStatus !== '')

            <h2>
                Turnamen Tidak Ditemukan
            </h2>

            <a href="{{ route('tournaments.index') }}" class="tr-btn tr-btn--outline">
                Tampilkan Semua
            </a>

            @else

            <h2>
                Belum Ada Turnamen
            </h2>

            @endif

        </div>

        @else

        <div class="tr-list tr-list--numbered">

            @foreach($tournaments as $tournament)

            @php
            // Nomor berlanjut pada halaman berikutnya.
            $nomor = $tournaments->firstItem()
            + $loop->index;

            $formatLabel =
            $tournament->format === 'liga'
            ? 'Liga'
            : 'Cup';

            $statusLabel = match ($tournament->status) {
            'active' => 'Berlangsung',
            'completed' => 'Selesai',
            'draft' => 'Belum Dimulai',
            default => 'Tidak Diketahui',
            };
            @endphp


            <div class="tr-list-row tr-index-row">

                {{-- KARTU TURNAMEN --}}
                <a href="{{ route('tournaments.show', $tournament) }}" class="tr-list-card tr-index-card">

                    {{-- NOMOR DI SEBELAH KIRI --}}
                    <span class="tr-index-number">
                        {{ $nomor }}
                    </span>


                    {{-- NAMA, FORMAT, STATUS --}}
                    <div class="tr-index-card__content">

                        <h2>
                            {{ $tournament->name }}
                        </h2>

                        <div class="tr-index-card__meta">

                            <span class="tr-index-format
                                        {{ $tournament->format === 'liga'
                                            ? 'tr-index-format--liga'
                                            : 'tr-index-format--cup' }}">

                                {{ $formatLabel }}

                            </span>

                            <span class="tr-badge tr-badge--{{ $tournament->status }}">
                                {{ $statusLabel }}
                            </span>

                        </div>

                    </div>


                    {{-- PANAH DETAIL --}}
                    <span class="tr-card-arrow" aria-hidden="true">
                        ›
                    </span>

                </a>


                {{-- TOMBOL HAPUS --}}
                @if($canManage)

                <form method="POST" action="{{ route('tournaments.destroy', $tournament) }}"
                    class="tr-delete-form tr-index-delete" data-single-submit
                    data-confirm="Hapus turnamen {{ $tournament->name }} beserta seluruh pertandingan dan hasilnya?"
                    data-confirm-title="Hapus Turnamen" data-confirm-text="Ya, Hapus">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="tr-delete-btn" title="Hapus Turnamen"
                        aria-label="Hapus {{ $tournament->name }}">

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                            <path d="M3 6h18" />
                            <path d="M8 6V4h8v2" />
                            <path d="M5 6l1 15h12l1-15" />
                            <path d="M10 10v7M14 10v7" />

                        </svg>

                    </button>

                </form>

                @endif

            </div>

            @endforeach

        </div>


        {{-- PAGINATION --}}
        @if($tournaments->hasPages())

        <div class="tr-pagination">
            {{ $tournaments->links() }}
        </div>

        @endif

        @endif

    </section>

</div>

@endsection
@push('scripts')
<script src="{{ asset('assets/js/tournament-index-filters.js') }}?v=1" defer>
</script>
@endpush