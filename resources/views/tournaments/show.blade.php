@extends('layouts.app')

@section('title', $tournament->name . ' - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=7">

<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=15">
@endpush

@section('content')

@php
$isLiga = $tournament->format === 'liga';
$isDraft = $tournament->status === 'draft';

$statusLabel = match ($tournament->status) {
'draft' => 'Draft',
'active' => 'Berlangsung',
'completed' => 'Selesai',
default => 'Tidak diketahui',
};

$tab = request()->query('bagian', 'detail');

if (!in_array($tab, [
'detail', 'bagan', 'pertandingan'
], true)) {
$tab = 'detail';
}

$totalPeserta = $tournament->entries->count();
$totalRonde = $rounds->count();

$roundNumbers = $rounds->keys()
->map(fn ($number) => (int) $number)
->values();

$requestedRound = request()->query('ronde');

$currentRound = (
is_scalar($requestedRound)
&& ctype_digit((string) $requestedRound)
&& $roundNumbers->contains((int) $requestedRound)
)
? (int) $requestedRound
: (int) ($roundNumbers->first() ?? 1);

$roundIndex = $roundNumbers->search($currentRound);

$previousRound = $roundIndex !== false && $roundIndex > 0
? $roundNumbers[$roundIndex - 1]
: null;

$nextRound = $roundIndex !== false
&& $roundIndex < $roundNumbers->count() - 1
    ? $roundNumbers[$roundIndex + 1]
    : null;

    $currentFixtures = $rounds->get(
    $currentRound,
    collect()
    );

    $roundLabel = $isLiga
    ? 'Ronde ' . $currentRound
    : match ($currentFixtures->count()) {
    1 => 'Final',
    2 => 'Semifinal',
    4 => 'Quarterfinal',
    default => 'Ronde ' . $currentRound,
    };

    // Data bagan Cup.
    $bracketRounds = [];

    if (!$isLiga && $tab === 'bagan') {
    $bracketRounds = $rounds
    ->values()
    ->map(function ($fixtures) use (
    $tournament,
    $canManage
    ) {
    return $fixtures->values()
    ->map(function ($fixture) use (
    $tournament,
    $canManage
    ) {
    $isBye = (bool) $fixture->is_bye;

    $score = $fixture->sets
    ->map(fn ($set) =>
    $set->a_score . ':' . $set->b_score
    )
    ->implode(' · ');

    if (
    $fixture->jus_a !== null
    && $fixture->jus_b !== null
    ) {
    $score .= ' · Jus '
    . $fixture->jus_a
    . ':' . $fixture->jus_b;
    }

    $ready = !$isBye
    && $fixture->a_entry_id !== null
    && $fixture->b_entry_id !== null;

    return [
    'a' => $fixture->entryA?->display_name
    ?? ($isBye ? 'BYE' : 'Menunggu'),

    'b' => $fixture->entryB?->display_name
    ?? ($isBye ? 'BYE' : 'Menunggu'),

    'a_id' => $fixture->a_entry_id,
    'b_id' => $fixture->b_entry_id,
    'winner_id' => $fixture->winner_entry_id,
    'score' => trim($score, ' ·'),

    'status' => $isBye
    ? 'Lolos otomatis'
    : ($fixture->result !== null
    ? 'Selesai'
    : 'Belum dimainkan'),


    'edit_url' => $canManage && $ready
    ? route('tournaments.fixtures.edit', [
    'tournament' => $tournament,
    'fixture' => $fixture,
    ])
    : null,
    ];
    })

    ->all();
    })
    ->all();
    }
    @endphp

    <div class="tr-page tr-detail-page">

        {{-- HEADER --}}
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
                    {{ $isLiga ? 'Liga' : 'Cup' }}
                    · {{ ucfirst($tournament->match_type) }}
                    · {{ $tournament->set_format_label }}
                </p>

            </div>

            <span class="tr-hero-status">
                {{ $statusLabel }}
            </span>
        </header>


        {{-- NAVIGASI --}}
        <nav class="tr-detail-tabs" aria-label="Bagian turnamen">

            <a href="{{ route('tournaments.show', [
                'tournament' => $tournament,
                'bagian' => 'detail',
            ]) }}" class="tr-detail-tab {{ $tab === 'detail' ? 'is-active' : '' }}" @if($tab==='detail' )
                aria-current="page" @endif>
                Detail
            </a>

            <a href="{{ route('tournaments.show', [
                'tournament' => $tournament,
                'bagian' => 'bagan',
            ]) }}" class="tr-detail-tab {{ $tab === 'bagan' ? 'is-active' : '' }}" @if($tab==='bagan' )
                aria-current="page" @endif>
                {{ $isLiga ? 'Klasemen' : 'Bagan' }}
            </a>

            <a href="{{ route('tournaments.show', [
                'tournament' => $tournament,
                'bagian' => 'pertandingan',
            ]) }}" class="tr-detail-tab {{ $tab === 'pertandingan' ? 'is-active' : '' }}" @if($tab==='pertandingan' )
                aria-current="page" @endif>
                Pertandingan
            </a>

        </nav>


        {{-- =========================================
        TAB DETAIL
        ========================================= --}}
        @if($tab === 'detail')

        @if($champion)
        <section class="tr-champion">
            <span aria-hidden="true">🏆</span>
            <div>
                <small>JUARA TURNAMEN</small>
                <strong>{{ $champion->display_name }}</strong>
            </div>
        </section>
        @endif

        <section class="tr-panel">

            <div class="tr-section-head">
                <div>
                    <span class="tr-eyebrow">
                        TOURNAMENT INFORMATION
                    </span>
                    <h2>Informasi Turnamen</h2>
                </div>

                <span class="tr-badge tr-badge--{{ $tournament->status }}">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="tr-detail-info-grid">

                <div class="tr-detail-info-item">
                    <small>Format</small>
                    <strong>{{ $isLiga ? 'Liga' : 'Cup' }}</strong>
                </div>

                <div class="tr-detail-info-item">
                    <small>Jenis Pertandingan</small>
                    <strong>
                        {{ ucfirst($tournament->match_type) }}
                    </strong>
                </div>

                <div class="tr-detail-info-item">
                    <small>Sistem Set</small>
                    <strong>
                        {{ $tournament->set_format_label }}
                    </strong>
                </div>

                <div class="tr-detail-info-item">
                    <small>Target Skor</small>
                    <strong>
                        {{ $tournament->target_points }} Poin
                    </strong>
                </div>

                <div class="tr-detail-info-item">
                    <small>Peserta</small>
                    <strong>{{ $totalPeserta }}</strong>
                </div>

                <div class="tr-detail-info-item">
                    <small>Ronde</small>
                    <strong>{{ $totalRonde }}</strong>
                </div>

                <div class="tr-detail-info-item">
                    <small>Pertandingan Selesai</small>
                    <strong>{{ $selesai }}</strong>
                </div>

                <div class="tr-detail-info-item">
                    <small>Belum Selesai</small>
                    <strong>{{ $belumSelesai }}</strong>
                </div>

            </div>

            @if($tournament->description)
            <div class="tr-detail-description">
                <h3>Deskripsi</h3>
                <p>{{ $tournament->description }}</p>
            </div>
            @endif

        </section>


        {{-- ATURAN LIGA --}}
        @if($isLiga)

        <section class="tr-panel">

            <div class="tr-section-head">
                <div>
                    <span class="tr-eyebrow">
                        LEAGUE RULES
                    </span>
                    <h2>Aturan Klasemen</h2>
                </div>
            </div>

            <div class="tr-league-info">

                @foreach([
                'Menang' => $tournament->league_win_points,
                'Seri' => $tournament->league_draw_points,
                'Kalah' => $tournament->league_loss_points,
                ] as $label => $points)

                <div class="tr-league-info__item">
                    <small>{{ $label }}</small>
                    <strong>
                        {{ $points > 0 ? '+' : '' }}{{ $points }}
                    </strong>
                </div>

                @endforeach

                <div class="tr-league-info__item">
                    <small>Pertemuan</small>
                    <strong>
                        {{ $tournament->league_legs ?? 1 }} Kali
                    </strong>
                </div>

            </div>

        </section>

        @endif


        {{-- DAFTAR PESERTA --}}
        <section class="tr-panel">

            <div class="tr-section-head">
                <div>
                    <span class="tr-eyebrow">
                        REGISTERED PARTICIPANTS
                    </span>
                    <h2>Daftar Peserta</h2>
                </div>

                <span class="tr-board-count">
                    {{ $totalPeserta }} Peserta
                </span>
            </div>

            <div class="tr-detail-entry-list">

                @forelse($tournament->entries as $entry)

                <div class="tr-detail-entry">

                    <span class="tr-detail-entry__number">
                        {{ $loop->iteration }}
                    </span>

                    <strong>
                        {{ $entry->display_name }}
                    </strong>

                </div>

                @empty

                <div class="tr-empty">
                    Belum ada peserta.
                </div>

                @endforelse

            </div>

        </section>


        {{-- DRAFT --}}
        @if($isDraft && $canManage)

        <section class="tr-detail-draft">

            <div>
                <strong>Turnamen belum dimulai</strong>
                <p>
                    Lanjutkan pengaturan peserta dan bagan.
                </p>
            </div>

            <a href="{{ route('tournaments.entries', $tournament) }}" class="tr-btn tr-btn--accent">
                Lanjutkan Pembuatan →
            </a>

        </section>

        @endif


        {{-- =========================================
        TAB BAGAN / KLASMEN
        ========================================= --}}
        @elseif($tab === 'bagan')

        @if($isLiga)

        <section class="tr-panel tr-table-section">

            <div class="tr-section-head">
                <div>
                    <span class="tr-eyebrow">
                        LEAGUE STANDINGS
                    </span>
                    <h2>Klasemen Liga</h2>
                </div>
            </div>

            <div class="tr-table-scroll" tabindex="0" role="region" aria-label="Klasemen Liga">


                <table class="tr-table tr-league-standings">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Peserta</th>
                            <th>Poin</th>
                            <th>Main</th>
                            <th>M</th>
                            <th>S</th>
                            <th>K</th>
                            <th>Selisih Set</th>
                            <th>Selisih Poin</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($standings as $row)

                        @php
                        $rank = $loop->iteration;

                        $rankClass = match ($rank) {
                        1 => 'tr-rank--first',
                        2 => 'tr-rank--second',
                        3 => 'tr-rank--third',
                        default => '',
                        };
                        @endphp

                        <tr class="{{ $rankClass }}">

                            {{-- NOMOR PERINGKAT --}}
                            <td class="tr-rank-cell">

                                <span class="tr-rank-number">

                                    @if($rank === 1)
                                    <svg class="tr-rank-crown" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">

                                        <path d="m2 8 5 4 5-7 5 7 5-4-2 11H4L2 8Z" />
                                        <path d="M4 22h16" />

                                    </svg>
                                    @endif

                                    {{ $rank }}

                                </span>

                            </td>

                            {{-- NAMA PESERTA --}}
                            <td class="tr-standings-name">

                                <strong>
                                    {{ $row['entry']->display_name }}
                                </strong>

                            </td>

                            {{-- POIN KLASEMEN --}}
                            <td class="tr-points-cell">

                                <strong class="tr-rank-points">
                                    {{ $row['league_points'] }}
                                </strong>

                            </td>

                            {{-- STATISTIK --}}
                            <td>{{ $row['played'] }}</td>
                            <td>{{ $row['wins'] }}</td>
                            <td>{{ $row['draws'] }}</td>
                            <td>{{ $row['losses'] }}</td>

                            {{-- SET MENANG - SET KALAH --}}
                            <td>

                                <span class="tr-for-against">

                                    <span class="tr-stat-for">
                                        {{ $row['sets_for'] }}
                                    </span>

                                    <span class="tr-stat-separator">
                                        −
                                    </span>

                                    <span class="tr-stat-against">
                                        {{ $row['sets_against'] }}
                                    </span>

                                </span>

                            </td>

                            {{-- POIN DIPEROLEH - POIN LAWAN --}}
                            <td>

                                <span class="tr-for-against">

                                    <span class="tr-stat-for">
                                        {{ $row['points_for'] }}
                                    </span>

                                    <span class="tr-stat-separator">
                                        −
                                    </span>

                                    <span class="tr-stat-against">
                                        {{ $row['points_against'] }}
                                    </span>

                                </span>

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

        </section>

        @else

        <section class="tr-panel">

            <div class="tr-section-head">
                <div>
                    <span class="tr-eyebrow">
                        CHAMPIONSHIP BRACKET
                    </span>
                    <h2>Bagan Pertandingan</h2>
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


        {{-- =========================================
        TAB PERTANDINGAN
        ========================================= --}}
        @elseif($tab === 'pertandingan')

        @include('tournaments._matches')

        @endif

    </div>

    @endsection

    @push('scripts')
    <script src="{{ asset('assets/js/tournaments.js') }}?v=13" defer>
    </script>
    @endpush