@extends('layouts.app')

@section('title', 'Bagan Pertandingan - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=10">
@endpush

@section('content')

@php
$isLiga = $tournament->format === 'liga';

$bracketRounds = [];

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
?? ($isBye ? 'BYE' : 'Menunggu pemenang'),

'b' => $b?->display_name
?? ($isBye ? 'BYE' : 'Menunggu pemenang'),

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
                ← Ubah Peserta
            </a>

            <span class="tr-eyebrow">
                PB ALFA / TOURNAMENT SETUP
            </span>

            <h1>
                {{ $isLiga ? 'JADWAL' : 'BAGAN' }}
                <span>PERTANDINGAN.</span>
            </h1>

        </div>

        <span class="tr-hero__decoration" aria-hidden="true">
            03
        </span>
    </header>

    @include('tournaments._steps', [
    'step' => 3
    ])

    {{-- VALIDASI --}}
    @if($errors->any())
    <div class="tr-alert" role="alert">
        <strong>Periksa susunan pertandingan.</strong>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- SUSUNAN PESERTA --}}
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
                $selectedEntry = $entryMap->get((int) $id);
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
    </section>

    {{-- HASIL GENERATE --}}
    <section class="tr-panel tr-schedule-preview" aria-labelledby="tr-preview-title">

        <div class="tr-section-head">
            <div>
                <span class="tr-eyebrow">
                    MATCH PREVIEW
                </span>

                <h2 id="tr-preview-title">
                    {{ $isLiga
                    ? 'Hasil Generate Jadwal'
                    : 'Hasil Generate Bagan' }}
                </h2>
            </div>
        </div>

        @if($isLiga)

        <div class="tr-league-preview-scroll tr-pinch-viewport" data-pinch-viewport tabindex="0" role="region"
            aria-label="Jadwal Liga yang dapat digeser dan diperbesar">

            <div class="tr-pinch-stage" data-pinch-stage>

                <div class="tr-pinch-content" data-pinch-content>

                    <div class="tr-round-grid">

                        @foreach($plan as $r => $matches)

                        <div class="tr-preview-round">

                            <h3>Ronde {{ $r + 1 }}</h3>

                            @forelse($matches as $m => $match)

                            @php
                            $a = $entryMap->get(
                            $match['a']
                            );

                            $b = $entryMap->get(
                            $match['b']
                            );
                            @endphp

                            <article class="tr-preview-match">

                                <span class="tr-preview-no">
                                    PERTANDINGAN {{ $m + 1 }}
                                </span>

                                <strong>
                                    {{ $a?->display_name ?? '—' }}
                                </strong>

                                <small>VS</small>

                                <strong>
                                    {{ $b?->display_name ?? '—' }}
                                </strong>

                            </article>

                            @empty

                            <div class="tr-empty">
                                Tidak ada pertandingan.
                            </div>

                            @endforelse

                        </div>

                        @endforeach

                    </div>

                </div>
            </div>
        </div>

        @else

        @if(count($bracketRounds) > 0)

        @include('tournaments._cup-bracket', [
        'bracketRounds' => $bracketRounds
        ])

        @else

        <div class="tr-empty">
            Bagan belum tersedia.
        </div>

        @endif

        @endif

        <p id="tr-preview-outdated" class="tr-hint tr-hint--error" role="status" hidden>
            Susunan berubah. Generate ulang bagan
            sebelum memulai turnamen.
        </p>

    </section>

    {{-- SELESAIKAN --}}
    <form id="tr-start-form" class="tr-finish" method="POST" action="{{ route('tournaments.start', $tournament) }}"
        data-single-submit data-confirm="Mulai turnamen dengan susunan peserta ini?" data-confirm-title="Mulai Turnamen"
        data-confirm-text="Ya, Mulai">

        @csrf

        @foreach($order as $id)
        <input type="hidden" name="order[]" value="{{ $id }}">
        @endforeach

        <button type="submit" class="tr-btn tr-btn--accent">
            Selesaikan &amp; Mulai Turnamen →
        </button>

    </form>

</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=10" defer>
</script>
@endpush