@extends('layouts.admin')

@section('title', 'Jadwal Turnamen - Admin PB Alfa')
@section('page_title', 'Jadwal Turnamen')

@section('content')

<div class="adm-page adm-tr-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                TOURNAMENT SETUP / LANGKAH 3
            </span>

            <h2>
                {{ $tournament->format === 'liga'
                ? 'Pratinjau Jadwal Liga'
                : 'Pratinjau Bagan Cup' }}
            </h2>

            <p>
                {{ $tournament->name }}
                · {{ count($order) }} peserta
            </p>
        </div>

        <a href="{{ route('admin.tournaments.entries', $tournament) }}" class="adm-btn adm-btn--secondary">
            Ubah Peserta
        </a>
    </div>

    <section class="adm-card adm-tr-form-card">

        <div class="adm-tr-section-title">
            <span>03</span>
            <div>
                <h3>Urutan Peserta</h3>
                <p>
                    Urutan ini digunakan oleh sistem
                    untuk menghasilkan jadwal atau bagan.
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.tournaments.schedule', $tournament) }}" data-admin-tr-order-form>

            <div class="adm-tr-order-grid">

                @foreach($order as $index => $entryId)

                <div class="adm-field">
                    <label for="tr-order-{{ $index }}">
                        Posisi {{ $index + 1 }}
                    </label>

                    <div class="adm-select-shell">
                        <select id="tr-order-{{ $index }}" name="order[]" class="adm-select adm-premium-select"
                            data-admin-premium-select data-tr-order>

                            @foreach($entryMap as $entry)
                            <option value="{{ $entry->id }}" @selected( (int) $entryId===(int) $entry->id
                                )>
                                {{ $entry->display_name }}
                            </option>
                            @endforeach

                        </select>
                    </div>
                </div>

                @endforeach

            </div>

            <div class="adm-tr-form-actions">
                <button type="button" class="adm-btn adm-btn--secondary" data-tr-shuffle>
                    Acak Urutan
                </button>

                <button type="submit" class="adm-btn adm-btn--primary">
                    Perbarui Pratinjau
                </button>
            </div>

        </form>
    </section>


    <section class="adm-card adm-tr-form-card">

        <div class="adm-tr-section-title">
            <span>04</span>
            <div>
                <h3>
                    {{ $tournament->format === 'liga'
                    ? 'Jadwal Pertandingan'
                    : 'Bagan Pertandingan' }}
                </h3>
                <p>
                    Berikut susunan yang akan disimpan
                    ketika turnamen dimulai.
                </p>
            </div>
        </div>

        <div class="adm-tr-rounds">

            @foreach($plan as $roundIndex => $matches)

            <div class="adm-tr-round">

                <div class="adm-tr-round__head">
                    <strong>Ronde {{ $roundIndex + 1 }}</strong>
                    <span>{{ count($matches) }} pertandingan</span>
                </div>

                <div class="adm-tr-round__matches">

                    @foreach($matches as $fixture)

                    @php
                    $aId = $fixture['a'] ?? null;
                    $bId = $fixture['b'] ?? null;

                    $isBye = (bool) (
                    $fixture['bye'] ?? false
                    );

                    $aName = $aId !== null
                    ? $entryMap->get($aId)?->display_name
                    : null;

                    $bName = $bId !== null
                    ? $entryMap->get($bId)?->display_name
                    : null;
                    @endphp

                    <div class="adm-tr-fixture">

                        <div class="adm-tr-fixture__team">
                            {{ $aName ?: ($isBye ? 'BYE' : 'Menunggu') }}
                        </div>

                        <div class="adm-tr-fixture__vs">
                            VS
                        </div>

                        <div class="adm-tr-fixture__team">
                            {{ $bName ?: ($isBye ? 'BYE' : 'Menunggu') }}
                        </div>

                        @if($isBye)
                        <span class="adm-pill adm-pill--warning">
                            Lolos Otomatis
                        </span>
                        @endif

                    </div>

                    @endforeach

                </div>

            </div>

            @endforeach

        </div>

    </section>


    <section class="adm-card adm-tr-start">

        <div>
            <h3>Mulai Turnamen?</h3>

            <p>
                Setelah dimulai, aturan dan daftar peserta
                tidak dapat diubah. Hasil pertandingan
                tetap dapat dikelola melalui panel admin.
            </p>

            <p class="adm-tr-stale" data-tr-order-stale hidden>
                Urutan peserta telah berubah.
                Perbarui pratinjau sebelum memulai turnamen.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.tournaments.start', $tournament) }}" data-single-submit
            data-confirm="Mulai turnamen dengan jadwal yang sedang ditampilkan? Aturan dan peserta akan terkunci."
            data-confirm-title="Mulai Turnamen" data-confirm-text="Ya, Mulai Turnamen">

            @csrf

            @foreach($order as $entryId)
            <input type="hidden" name="order[]" value="{{ $entryId }}">
            @endforeach

            <button type="submit" class="adm-btn adm-btn--primary" data-tr-start>
                Mulai Turnamen
            </button>
        </form>

    </section>

</div>

@endsection