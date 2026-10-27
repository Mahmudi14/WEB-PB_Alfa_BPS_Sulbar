@extends('layouts.app')

@section('title', 'Persiapan Turnamen - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=20">
@endpush

@section('content')

@php
$isLiga = $tournament->format === 'liga';

$bracketRounds = [];

// Bagan hanya untuk Cup.
if (!$isLiga) {
$bracketRounds = collect($plan)
->map(function ($matches) use ($entryMap) {
return collect($matches)
->map(function ($match) use ($entryMap) {

$aId = $match['a'] ?? null;
$bId = $match['b'] ?? null;
$isBye = (bool) ($match['bye'] ?? false);

$a = $aId !== null
? $entryMap->get($aId)
: null;

$b = $bId !== null
? $entryMap->get($bId)
: null;

return [
'a' => $a?->display_name
?? ($isBye ? 'BYE' : 'Menunggu'),

'b' => $b?->display_name
?? ($isBye ? 'BYE' : 'Menunggu'),

'a_id' => $aId,
'b_id' => $bId,
'winner_id' => null,
'score' => '',

'status' => $isBye
? 'Lolos otomatis'
: 'Belum dimainkan',

'edit_url' => null,
];
})
->values()
->all();
})
->values()
->all();
}
@endphp

<div class="tr-page">

    {{-- HEADER --}}
    <header class="tr-page-heading">

        <div class="tr-page-heading__main">

            <a href="{{ route('tournaments.entries', $tournament) }}" class="tr-back">
                ← Ubah Pemain
            </a>

            <span class="tr-eyebrow">
                PB ALFA / TOURNAMENT SETUP
            </span>

            @if($isLiga)
            <h1>
                DAFTAR <span>PEMAIN.</span>
            </h1>
            @else
            <h1>
                BAGAN <span>PERTANDINGAN.</span>
            </h1>
            @endif

        </div>

        <span class="tr-hero__decoration" aria-hidden="true">
            03
        </span>

    </header>

    {{-- TAHAP PEMBUATAN --}}
    @include('tournaments._steps', [
    'step' => 3,
    'isLiga' => $isLiga
    ])

    {{-- VALIDASI --}}
    @if($errors->any())
    <div class="tr-alert" role="alert">

        <strong>Periksa kembali data turnamen.</strong>

        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>
    @endif


    {{-- =====================================
    MODE LIGA: DAFTAR PEMAIN SAJA
    ===================================== --}}
    @if($isLiga)

    <section class="tr-panel" aria-labelledby="tr-league-players-title">

        <div class="tr-section-head">

            <div>
                <span class="tr-eyebrow">
                    DAFTAR PESERTA
                </span>

                <h2 id="tr-league-players-title">
                    Pemain Liga
                </h2>
            </div>

            <span class="tr-board-count">
                {{ count($order) }} Peserta
            </span>

        </div>

        {{-- HANYA MENAMPILKAN PEMAIN --}}
        <div class="tr-detail-entry-list">

            @foreach($order as $i => $id)

            @php
            $entry = $entryMap->get((int) $id);
            @endphp

            <div class="tr-detail-entry">

                <span class="tr-detail-entry__number">
                    {{ $i + 1 }}
                </span>

                <strong>
                    {{ $entry?->display_name ?? 'Menunggu' }}
                </strong>

            </div>

            @endforeach

        </div>

    </section>


    {{-- =====================================
    MODE CUP: SUSUNAN DAN BAGAN
    ===================================== --}}
    @else

    {{-- SUSUNAN PESERTA CUP --}}
    <section class="tr-panel" aria-labelledby="tr-order-title">

        <div class="tr-section-head">

            <div>
                <span class="tr-eyebrow">
                    TOURNAMENT SETUP
                </span>

                <h2 id="tr-order-title">
                    Susunan Peserta
                </h2>
            </div>

        </div>

        <form id="tr-preview-form" method="GET" action="{{ route('tournaments.schedule', $tournament) }}">

            <div class="tr-order-grid">

                @foreach($order as $i => $id)

                @php
                $selectedEntry = $entryMap->get(
                (int) $id
                );
                @endphp

                <div class="tr-field">

                    <label id="tr-order-label-{{ $i }}">
                        Posisi {{ $i + 1 }}
                    </label>

                    <div class="tr-order-picker" data-order-picker>

                        <input type="hidden" name="order[]" data-order-value value="{{ $id }}">

                        <button type="button" class="tr-order-trigger is-selected" data-order-trigger
                            aria-expanded="false" aria-labelledby="tr-order-label-{{ $i }} tr-order-selected-{{ $i }}">

                            <span id="tr-order-selected-{{ $i }}" class="tr-order-label" data-order-label>

                                {{ $selectedEntry?->display_name ?? 'Pilih peserta' }}

                            </span>

                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                                <path d="m6 9 6 6 6-6" />

                            </svg>

                        </button>

                        {{-- DROPDOWN PENCARIAN --}}
                        <div class="tr-order-menu" data-order-menu hidden>

                            <input type="search" class="tr-order-search" data-order-search placeholder="Cari peserta..."
                                aria-label="Cari peserta" autocomplete="off">

                            <div class="tr-order-options">

                                @foreach($entryMap as $entry)

                                <button type="button" class="tr-order-option" data-order-option
                                    data-id="{{ $entry->id }}" data-name="{{ $entry->display_name }}">

                                    {{ $entry->display_name }}

                                </button>

                                @endforeach

                            </div>

                            <p class="tr-order-empty" data-order-empty hidden>
                                Peserta tidak ditemukan.
                            </p>

                        </div>

                    </div>

                </div>

                @endforeach

            </div>

            {{-- TOMBOL KHUSUS CUP --}}
            <div class="tr-actions tr-order-actions">

                <button type="button" id="tr-shuffle" class="tr-btn tr-btn--outline">
                    ↻ Acak Peserta
                </button>

                <button type="submit" class="tr-btn">
                    Generate Bagan
                </button>

            </div>

            <p id="tr-order-error" class="tr-hint tr-hint--error" role="alert" hidden>
            </p>

        </form>

        <p id="tr-preview-outdated" class="tr-hint tr-hint--error" role="status" hidden>
            Susunan berubah. Generate ulang bagan
            sebelum memulai turnamen.
        </p>

    </section>


    {{-- BAGAN CUP --}}
    <section class="tr-panel tr-schedule-preview" aria-labelledby="tr-preview-title">

        <div class="tr-section-head">

            <div>
                <span class="tr-eyebrow">
                    MATCH PREVIEW
                </span>

                <h2 id="tr-preview-title">
                    Hasil Generate Bagan
                </h2>
            </div>

        </div>

        @if(count($bracketRounds) > 0)

        @include('tournaments._cup-bracket', [
        'bracketRounds' => $bracketRounds
        ])

        @else

        <div class="tr-empty">
            Bagan belum tersedia.
        </div>

        @endif

    </section>

    @endif


    {{-- =====================================
    TOMBOL MULAI DI BAWAH DAFTAR
    ===================================== --}}
    <form id="tr-start-form" class="tr-finish" method="POST" action="{{ route('tournaments.start', $tournament) }}"
        data-single-submit data-confirm="{{ $isLiga
            ? 'Mulai Liga dengan daftar pemain ini?'
            : 'Mulai Cup dengan bagan ini?' }}" data-confirm-title="Mulai Turnamen" data-confirm-text="Ya, Mulai">

        @csrf

        {{-- Susunan asli tetap dikirim ke backend --}}
        @foreach($order as $id)

        <input type="hidden" name="order[]" value="{{ $id }}">

        @endforeach

        <button type="submit" class="tr-btn tr-btn--accent">

            {{ $isLiga
            ? 'Mulai Liga →'
            : 'Selesaikan & Mulai Cup →' }}

        </button>

    </form>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=20" defer>
</script>
@endpush