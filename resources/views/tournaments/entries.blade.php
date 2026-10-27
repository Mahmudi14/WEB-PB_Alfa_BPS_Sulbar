@extends('layouts.app')

@section('title', 'Atur Pemain - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=6">
@endpush

@section('content')

@php
$isGanda = $tournament->match_type === 'ganda';

$slots = $isGanda ? [0, 1] : [0];

$slotTitles = $isGanda
? ['Pemain 1', 'Pemain 2']
: ['Pilih Pemain'];

$playerMap = $players->keyBy('id');

$existing = old('entries');

if (!is_array($existing)) {
$existing = $tournament->entries
->map(fn ($entry) => [
'players' => $entry->players
->pluck('id')
->all()
])
->all();
}

while (count($existing) < 2) { $existing[]=['players'=> []];
    }
    @endphp

    <div class="tr-page tr-page--narrow">

        {{-- HEADER --}}
        <header class="tr-page-heading">

            <div class="tr-page-heading__main">

                <a href="{{ route('tournaments.rules.edit', $tournament) }}" class="tr-back">
                    ← Ubah Aturan
                </a>

                <span class="tr-eyebrow">
                    PB ALFA / TOURNAMENT SETUP
                </span>

                <h1>ATUR <span>PEMAIN.</span></h1>

                <p>
                    Langkah 2 dari 4 —
                    {{ $isGanda
                    ? 'Tentukan dua pemain untuk setiap peserta ganda.'
                    : 'Pilih pemain yang mengikuti turnamen.'
                    }}
                    Minimal 2 dan maksimal 32 peserta.
                </p>

            </div>

            <span class="tr-hero__decoration" aria-hidden="true">
                02
            </span>

        </header>

        {{-- LANGKAH PEMBUATAN --}}
        @include('tournaments._steps', [
        'step' => 2
        ])

        {{-- FORM PESERTA --}}
        <form id="tr-entries-form" class="tr-panel tr-form" method="POST"
            action="{{ route('tournaments.entries.save', $tournament) }}" data-single-submit
            data-type="{{ $tournament->match_type }}">

            @csrf
            @method('PUT')

            {{-- ERROR SERVER --}}
            @if($errors->any())

            <div class="tr-alert" role="alert">

                <strong>
                    Periksa kembali peserta turnamen.
                </strong>

                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>

            @endif

            {{-- HEADING --}}
            <div class="tr-form-section-title">

                <div>

                    <span class="tr-eyebrow">
                        DAFTAR PESERTA
                    </span>

                    <h2>
                        {{ $isGanda
                        ? 'Peserta Ganda'
                        : 'Peserta Tunggal'
                        }}
                    </h2>

                </div>

                <small class="tr-hint">
                    Setiap pemain hanya boleh dipilih sekali.
                </small>

            </div>

            {{-- DAFTAR PESERTA --}}
            <div id="tr-entry-rows" class="tr-entry-rows">

                @foreach($existing as $i => $entry)

                <div class="tr-entry" data-entry-row>

                    <div class="tr-entry-heading">

                        <strong>
                            {{ $isGanda ? 'Peserta Ganda' : 'Peserta' }}
                            <span data-entry-label>
                                {{ $loop->iteration }}
                            </span>
                        </strong>

                        <button type="button" class="tr-text-btn" data-entry-remove>
                            Hapus
                        </button>

                    </div>

                    <div
                        class="tr-entry-selects {{ $isGanda ? 'tr-entry-selects--double' : 'tr-entry-selects--single' }}">

                        @foreach($slots as $slot)

                        @php
                        $selectedId = (string) data_get(
                        $entry,
                        'players.' . $slot,
                        ''
                        );

                        $selectedPlayer = $selectedId !== ''
                        ? $playerMap->get((int) $selectedId)
                        : null;
                        @endphp

                        <div class="tr-entry-slot">


                            <div class="tr-field">

                                <label class="tr-player-field-label">
                                    {{ $slotTitles[$slot] ?? 'Pilih Pemain' }}
                                </label>

                                {{-- CUSTOM PLAYER PICKER --}}
                                <div class="tr-player-picker" data-player-picker data-slot="{{ $slot }}">

                                    <input type="hidden" data-player-value
                                        name="entries[{{ $i }}][players][{{ $slot }}]" value="{{ $selectedId }}">

                                    {{-- TOMBOL PILIH PEMAIN --}}
                                    <button type="button"
                                        class="tr-player-trigger {{ $selectedPlayer ? 'is-selected' : '' }}"
                                        data-player-trigger aria-haspopup="true" aria-expanded="false">

                                        <span class="tr-player-label" data-player-label>

                                            {{ $selectedPlayer?->name
                                            ?? 'Pilih pemain'
                                            }}

                                        </span>

                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                                            <path d="m6 9 6 6 6-6" />

                                        </svg>

                                    </button>

                                    {{-- MENU PENCARIAN --}}
                                    <div class="tr-player-menu" data-player-menu hidden>

                                        <input type="search" class="tr-player-search" data-player-search
                                            placeholder="Cari nama pemain..." aria-label="Cari nama pemain"
                                            autocomplete="off">

                                        <div class="tr-player-options">

                                            @foreach($players as $player)

                                            <button type="button" class="tr-player-option" data-player-option
                                                data-id="{{ $player->id }}" data-name="{{ $player->name }}">

                                                {{ $player->name }}

                                            </button>

                                            @endforeach

                                        </div>

                                        <p class="tr-player-empty" data-player-empty hidden>
                                            Pemain tidak ditemukan atau
                                            sudah dipilih.
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                        @endforeach

                    </div>

                </div>

                @endforeach

            </div>

            {{-- TEMPLATE PESERTA BARU --}}
            <template id="tr-entry-template">

                <div class="tr-entry" data-entry-row>

                    <div class="tr-entry-heading">

                        <strong>
                            {{ $isGanda ? 'Peserta Ganda' : 'Peserta' }}
                            <span data-entry-label></span>
                        </strong>

                        <button type="button" class="tr-text-btn" data-entry-remove>
                            Hapus
                        </button>

                    </div>

                    <div
                        class="tr-entry-selects {{ $isGanda ? 'tr-entry-selects--double' : 'tr-entry-selects--single' }}">

                        @foreach($slots as $slot)

                        <div class="tr-entry-slot">


                            <div class="tr-field">

                                <label class="tr-player-field-label">
                                    {{ $slotTitles[$slot] ?? 'Pilih Pemain' }}
                                </label>

                                <div class="tr-player-picker" data-player-picker data-slot="{{ $slot }}">

                                    <input type="hidden" data-player-value value="">

                                    <button type="button" class="tr-player-trigger" data-player-trigger
                                        aria-haspopup="true" aria-expanded="false">

                                        <span class="tr-player-label" data-player-label>
                                            Pilih pemain
                                        </span>

                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">

                                            <path d="m6 9 6 6 6-6" />

                                        </svg>

                                    </button>

                                    <div class="tr-player-menu" data-player-menu hidden>

                                        <input type="search" class="tr-player-search" data-player-search
                                            placeholder="Cari nama pemain..." aria-label="Cari nama pemain"
                                            autocomplete="off">

                                        <div class="tr-player-options">

                                            @foreach($players as $player)

                                            <button type="button" class="tr-player-option" data-player-option
                                                data-id="{{ $player->id }}" data-name="{{ $player->name }}">

                                                {{ $player->name }}

                                            </button>

                                            @endforeach

                                        </div>

                                        <p class="tr-player-empty" data-player-empty hidden>
                                            Pemain tidak ditemukan atau
                                            sudah dipilih.
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                        @endforeach

                    </div>

                </div>

            </template>

            {{-- TOMBOL TAMBAH PESERTA --}}
            <div class="tr-actions">

                <button type="button" class="tr-btn tr-btn--outline" id="tr-add-entry">

                    + Tambah
                    {{ $isGanda ? 'Peserta Ganda' : 'Peserta' }}

                </button>

            </div>

            {{-- VALIDASI CLIENT --}}
            <p id="tr-entry-error" class="tr-hint tr-hint--error" role="alert" hidden>
            </p>

            {{-- AKSI --}}
            <div class="tr-form-footer">

                <a href="{{ route('tournaments.index') }}" class="tr-btn tr-btn--muted">
                    Simpan Nanti
                </a>

                <button type="submit" class="tr-btn">
                    {{ $tournament->format === 'liga'
                    ? 'Berikutnya: Lihat Daftar Pemain →'
                    : 'Berikutnya: Lihat Bagan →' }}
                </button>

            </div>

        </form>

    </div>

    @endsection

    @push('scripts')
    <script src="{{ asset('assets/js/tournaments.js') }}?v=6" defer>
    </script>
    @endpush