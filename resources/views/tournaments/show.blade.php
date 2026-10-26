@extends('layouts.app')

@section('title', $tournament->name . ' - Turnamen PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=3">
@endpush

@section('content')

@php
$isLiga = $tournament->format === 'liga';

$statusLabel = match ($tournament->status) {
'completed' => 'Selesai',
'active' => 'Berlangsung',
default => 'Persiapan',
};

$totalPeserta = $tournament->entries->count();
$totalPertandingan = $tournament->fixtures->count();
$totalRonde = $rounds->count();

$jumlahPertemuan = (int) ($tournament->league_legs ?? 1);

$poinMenang = (int) ($tournament->league_win_points ?? 3);
$poinSeri = (int) ($tournament->league_draw_points ?? 1);
$poinKalah = (int) ($tournament->league_loss_points ?? 0);
@endphp

<div class="tr-page">

    {{-- HEADER TURNAMEN --}}
    <header class="tr-hero">

        <div class="tr-hero__main">

            <a href="{{ route('tournaments.index') }}" class="tr-back">
                ← Semua Turnamen
            </a>

            <span class="tr-eyebrow">
                PB ALFA /
                {{ $isLiga ? 'LEAGUE CENTER' : 'CUP CENTER' }}
            </span>

            <h1>{{ $tournament->name }}</h1>

            <p>
                {{ ucfirst($tournament->match_type) }}
                · {{ $tournament->set_format_label }}
                · Target {{ $tournament->target_points }} Poin
            </p>

            @if($tournament->description)
            <p class="tr-description">
                {{ $tournament->description }}
            </p>
            @endif

        </div>

        <span class="tr-hero-status">
            {{ $statusLabel }}
        </span>

    </header>


    {{-- JUARA TURNAMEN --}}
    @if($champion)

    <section class="tr-champion">

        <span aria-hidden="true">🏆</span>

        <div>
            <small>JUARA TURNAMEN</small>

            <strong>
                {{ $champion->display_name }}
            </strong>
        </div>

    </section>

    @endif


    {{-- RINGKASAN TURNAMEN --}}
    <section class="tr-summary" aria-label="Ringkasan turnamen">

        <div>
            <small>Format</small>
            <strong>
                {{ $isLiga ? 'Liga' : 'Cup' }}
            </strong>
        </div>

        <div>
            <small>Peserta</small>
            <strong>{{ $totalPeserta }}</strong>
        </div>

        <div>
            <small>Ronde</small>
            <strong>{{ $totalRonde }}</strong>
        </div>

        <div>
            @if($isLiga)

            <small>Pertemuan</small>

            <strong>
                {{ $jumlahPertemuan }} Kali
            </strong>

            @else

            <small>Target Skor</small>

            <strong>
                {{ $tournament->target_points }} Poin
            </strong>

            @endif
        </div>

    </section>


    {{-- KLASMEN KHUSUS LIGA --}}
    @if($isLiga)

    <section class="tr-panel tr-table-section" aria-labelledby="tr-standings-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    LEAGUE STANDINGS
                </span>

                <h2 id="tr-standings-title">
                    Klasemen Liga
                </h2>

            </div>

            <span class="tr-board-count">
                {{ $totalPeserta }} Peserta
            </span>

        </div>


        {{-- KETERANGAN ATURAN LIGA --}}
        <div class="tr-league-info">

            <div class="tr-league-info__item">
                <small>Menang</small>
                <strong>{{ $poinMenang > 0 ? '+' : '' }}{{ $poinMenang }}</strong>
            </div>

            <div class="tr-league-info__item">
                <small>Seri</small>
                <strong>{{ $poinSeri > 0 ? '+' : '' }}{{ $poinSeri }}</strong>
            </div>

            <div class="tr-league-info__item">
                <small>Kalah</small>
                <strong>{{ $poinKalah > 0 ? '+' : '' }}{{ $poinKalah }}</strong>
            </div>

            <div class="tr-league-info__item">
                <small>Pertemuan</small>
                <strong>{{ $jumlahPertemuan }} Kali</strong>
            </div>

        </div>


        {{-- TABEL KLASMEN --}}
        <div class="tr-table-scroll" tabindex="0" role="region"
            aria-label="Klasemen Liga, geser ke samping untuk melihat semua kolom">

            <table class="tr-table">

                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Peserta</th>
                        <th scope="col">Main</th>
                        <th scope="col">M</th>
                        <th scope="col">S</th>
                        <th scope="col">K</th>
                        <th scope="col">Selisih Set</th>
                        <th scope="col">Selisih Poin</th>
                        <th scope="col">Poin</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($standings as $row)

                    @php
                    $selisihSet =
                    $row['sets_for']
                    - $row['sets_against'];

                    $selisihPoin =
                    $row['points_for']
                    - $row['points_against'];

                    $poinKlasemen =
                    (int) $row['league_points'];
                    @endphp

                    <tr>

                        {{-- POSISI --}}
                        <td>
                            {{ $loop->iteration }}
                        </td>

                        {{-- NAMA PESERTA --}}
                        <td class="tr-standings-name">
                            {{ $row['entry']->display_name }}
                        </td>

                        {{-- JUMLAH MAIN --}}
                        <td>
                            {{ $row['played'] }}
                        </td>

                        {{-- MENANG --}}
                        <td>
                            {{ $row['wins'] }}
                        </td>

                        {{-- SERI --}}
                        <td>
                            {{ $row['draws'] }}
                        </td>

                        {{-- KALAH --}}
                        <td>
                            {{ $row['losses'] }}
                        </td>

                        {{-- SELISIH SET --}}
                        <td>
                            {{ $selisihSet > 0 ? '+' : '' }}{{ $selisihSet }}
                        </td>

                        {{-- SELISIH POIN --}}
                        <td>
                            {{ $selisihPoin > 0 ? '+' : '' }}{{ $selisihPoin }}
                        </td>

                        {{-- POIN KLASMEN --}}
                        <td>
                            <strong>
                                {{ $poinKlasemen }}
                            </strong>
                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="9">
                            Belum ada peserta Liga.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <p class="tr-hint" style="margin: 10px 0 0;">
            Di HP, geser tabel ke samping untuk melihat
            seluruh statistik. Poin klasemen dapat bernilai
            negatif sesuai aturan Liga.
        </p>

    </section>

    @endif


    {{-- DAFTAR PERTANDINGAN PER RONDE --}}
    <section class="tr-panel" data-round-widget aria-labelledby="tr-fixtures-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    MATCH CENTER
                </span>

                <h2 id="tr-fixtures-title">
                    {{ $isLiga
                    ? 'Jadwal Pertandingan'
                    : 'Bagan Pertandingan'
                    }}
                </h2>

            </div>

            <span class="tr-board-count">
                {{ $totalPertandingan }} Pertandingan
            </span>

        </div>


        @if($totalRonde > 0)

        {{-- NAVIGASI RONDE --}}
        <div class="tr-round-nav" aria-label="Navigasi ronde">

            <button type="button" class="tr-round-arrow" data-round-prev aria-label="Ronde sebelumnya">
                ‹
            </button>

            <h3 data-round-title aria-live="polite">
                Ronde 1
            </h3>

            <button type="button" class="tr-round-arrow" data-round-next aria-label="Ronde berikutnya">
                ›
            </button>

        </div>


        {{-- ISI TIAP RONDE --}}
        <div class="tr-round-panes">

            @foreach($rounds as $roundNumber => $fixtures)

            <div data-round-pane data-round-number="{{ $roundNumber }}" @if(!$loop->first) hidden @endif>

                <div class="tr-match-grid">

                    @forelse($fixtures as $fixture)

                    @include('tournaments._fixture', [
                    'fixture' => $fixture
                    ])

                    @empty

                    <div class="tr-empty">
                        <strong>
                            Belum ada pertandingan
                            pada ronde ini.
                        </strong>
                    </div>

                    @endforelse

                </div>

            </div>

            @endforeach

        </div>

        @else

        <div class="tr-empty">
            <strong>
                Jadwal pertandingan belum tersedia.
            </strong>
        </div>

        @endif

    </section>


    {{-- INFORMASI RANKING --}}
    <p class="tr-footnote">
        Hasil turnamen disimpan terpisah dari
        pertandingan harian dan tidak mengubah
        ranking umum pemain PB Alfa.
    </p>

</div>

@endsection


@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=3" defer>
</script>
@endpush