@extends('layouts.app')

@section('title', $tournament->name . ' - Turnamen PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=5">
@endpush

@section('content')

@php
$isLiga = $tournament->format === 'liga';

$statusLabel = match ($tournament->status) {
'draft' => 'Draft',
'active' => 'Berlangsung',
'completed' => 'Selesai',
default => 'Tidak Diketahui',
};

$totalPeserta = $tournament->entries->count();
$totalPertandingan = $tournament->fixtures->count();
$totalRonde = $rounds->count();

$jumlahPertemuan = (int) ($tournament->league_legs ?? 1);

$poinMenang = (int) ($tournament->league_win_points ?? 3);
$poinSeri = (int) ($tournament->league_draw_points ?? 1);
$poinKalah = (int) ($tournament->league_loss_points ?? 0);

/*
* Siapkan data bagan Cup untuk komponen
* _cup-bracket.blade.php.
*
* Urutan pertandingan mengikuti round_number
* dan match_number dari database.
*/
$bracketRounds = [];

if (!$isLiga && $totalRonde > 0) {

$bracketRounds = $rounds
->values()
->map(function ($fixtures) use (
$tournament,
$canManage
) {
return $fixtures
->values()
->map(function ($fixture) use (
$tournament,
$canManage
) {

$isBye = (bool) $fixture->is_bye;

$score = $fixture->sets
->map(
fn ($set) =>
$set->a_score . ' : ' . $set->b_score
)
->implode(' · ');

// Tambahkan skor jus jika ada.
if (
$fixture->jus_a !== null
&& $fixture->jus_b !== null
) {
$jusText = 'Jus '
. $fixture->jus_a
. ' : '
. $fixture->jus_b;

$score = trim(
$score . ' · ' . $jusText,
' ·'
);
}

$canEdit = $canManage
&& !$isBye
&& $fixture->a_entry_id !== null
&& $fixture->b_entry_id !== null;

return [
'a' => $fixture->entryA?->display_name
?? ($isBye
? 'BYE'
: 'Menunggu pemenang'),

'b' => $fixture->entryB?->display_name
?? ($isBye
? 'BYE'
: 'Menunggu pemenang'),

'a_id' => $fixture->a_entry_id,
'b_id' => $fixture->b_entry_id,

'winner_id' => $fixture->winner_entry_id,

'score' => $score,

'status' => $isBye
? 'Lolos otomatis'
: (
$fixture->result !== null
? 'Selesai'
: (
$fixture->a_entry_id
&& $fixture->b_entry_id
? 'Belum dimainkan'
: 'Menunggu peserta'
)
),

'edit_url' => $canEdit
? route(
'tournaments.fixtures.edit',
[$tournament, $fixture]
)
: null,
];
})
->all();
})
->all();
}
@endphp

<div class="tr-page">

    {{-- =========================================
    HEADER TURNAMEN
    ========================================= --}}
    <header class="tr-hero">

        <div class="tr-hero__main">

            <a href="{{ route('tournaments.index') }}" class="tr-back">
                ← Semua Turnamen
            </a>

            <span class="tr-eyebrow">
                PB ALFA /
                {{ $isLiga
                ? 'LEAGUE CENTER'
                : 'CUP CENTER' }}
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


    {{-- =========================================
    TOMBOL HAPUS TURNAMEN
    ========================================= --}}
    @if($canManage)

    <section class="tr-panel" aria-labelledby="tr-management-title">

        <div class="tr-section-head">

            <div>
                <span class="tr-eyebrow">
                    TOURNAMENT MANAGEMENT
                </span>

                <h2 id="tr-management-title">
                    Pengelolaan Turnamen
                </h2>
            </div>

        </div>

        <p class="tr-hint">
            Admin dan pengelola dapat menghapus
            turnamen beserta seluruh peserta,
            jadwal, dan hasil pertandingannya.
            Penghapusan tidak dapat dibatalkan.
        </p>

        <form method="POST" action="{{ route('tournaments.destroy', $tournament) }}" class="tr-delete-form"
            data-single-submit
            data-confirm="Hapus turnamen {{ $tournament->name }}? Seluruh peserta, jadwal, hasil dan skor turnamen akan dihapus secara permanen."
            data-confirm-title="Hapus Turnamen" data-confirm-text="Ya, Hapus">

            @csrf
            @method('DELETE')

            <button type="submit" class="tr-delete-btn">

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">

                    <path d="M3 6h18" />
                    <path d="M8 6V4h8v2" />
                    <path d="M5 6l1 15h12l1-15" />
                    <path d="M10 10v7M14 10v7" />

                </svg>

                Hapus Turnamen

            </button>

        </form>

    </section>

    @endif


    {{-- =========================================
    JUARA TURNAMEN
    ========================================= --}}
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


    {{-- =========================================
    RINGKASAN TURNAMEN
    ========================================= --}}
    <section class="tr-summary" aria-label="Ringkasan turnamen">

        <div>
            <small>Format</small>

            <strong>
                {{ $isLiga ? 'Liga' : 'Cup' }}
            </strong>
        </div>

        <div>
            <small>Peserta</small>

            <strong>
                {{ $totalPeserta }}
            </strong>
        </div>

        <div>
            <small>Ronde</small>

            <strong>
                {{ $totalRonde }}
            </strong>
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


    {{-- =========================================
    KLASMEN LIGA
    ========================================= --}}
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


        {{-- Aturan poin Liga --}}
        <div class="tr-league-info">

            <div class="tr-league-info__item">

                <small>Menang</small>

                <strong>
                    {{ $poinMenang > 0 ? '+' : '' }}{{ $poinMenang }}
                </strong>

            </div>

            <div class="tr-league-info__item">

                <small>Seri</small>

                <strong>
                    {{ $poinSeri > 0 ? '+' : '' }}{{ $poinSeri }}
                </strong>

            </div>

            <div class="tr-league-info__item">

                <small>Kalah</small>

                <strong>
                    {{ $poinKalah > 0 ? '+' : '' }}{{ $poinKalah }}
                </strong>

            </div>

            <div class="tr-league-info__item">

                <small>Pertemuan</small>

                <strong>
                    {{ $jumlahPertemuan }} Kali
                </strong>

            </div>

        </div>


        {{-- Tabel klasemen --}}
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

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td class="tr-standings-name">
                            {{ $row['entry']->display_name }}
                        </td>

                        <td>{{ $row['played'] }}</td>

                        <td>{{ $row['wins'] }}</td>

                        <td>{{ $row['draws'] }}</td>

                        <td>{{ $row['losses'] }}</td>

                        <td>
                            {{ $selisihSet > 0 ? '+' : '' }}{{ $selisihSet }}
                        </td>

                        <td>
                            {{ $selisihPoin > 0 ? '+' : '' }}{{ $selisihPoin }}
                        </td>

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
            Geser tabel ke samping pada HP untuk
            melihat seluruh statistik.
            Poin klasemen dapat bernilai negatif.
        </p>

    </section>

    @endif


    {{-- =========================================
    BAGAN PERTANDINGAN CUP
    GRUP A - GRAND FINAL - GRUP B
    ========================================= --}}
    @if(!$isLiga && $totalRonde > 0)

    <section class="tr-panel" aria-labelledby="tr-bracket-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    CHAMPIONSHIP BRACKET
                </span>

                <h2 id="tr-bracket-title">
                    Bagan Pertandingan Cup
                </h2>

            </div>

            <span class="tr-board-count">
                {{ $totalPeserta }} Peserta
            </span>

        </div>

        @include('tournaments._cup-bracket', [
        'bracketRounds' => $bracketRounds
        ])

    </section>

    @endif


    {{-- =========================================
    JADWAL LIGA PER RONDE
    ========================================= --}}
    @if($isLiga || $totalRonde === 0)

    <section class="tr-panel" data-round-widget aria-labelledby="tr-fixtures-title">

        <div class="tr-section-head">

            <div>

                <span class="tr-eyebrow">
                    MATCH CENTER
                </span>

                <h2 id="tr-fixtures-title">
                    {{ $isLiga
                    ? 'Jadwal Pertandingan'
                    : 'Bagan Pertandingan' }}
                </h2>

            </div>

            <span class="tr-board-count">
                {{ $totalPertandingan }} Pertandingan
            </span>

        </div>


        @if($totalRonde > 0)

        {{-- Navigasi ronde --}}
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

    @endif


    {{-- =========================================
    CATATAN RANKING
    ========================================= --}}
    <p class="tr-footnote">
        Hasil turnamen disimpan terpisah dari
        pertandingan harian dan tidak mengubah
        ranking umum pemain PB Alfa.
    </p>

</div>

@endsection


@push('scripts')
<script src="{{ asset('assets/js/tournaments.js') }}?v=5" defer>
</script>
@endpush