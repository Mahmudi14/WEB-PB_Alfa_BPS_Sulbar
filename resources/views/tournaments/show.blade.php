@extends('layouts.app')

@section('title', $tournament->name . ' - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=13">
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
    ? route('tournaments.show', [
    'tournament' => $tournament,
    'bagian' => 'pertandingan',
    'ronde' => $fixture->round_number,
    'edit' => $fixture->id,
    ]) . '#fixture-' . $fixture->id
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

                <table class="tr-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Peserta</th>
                            <th>Main</th>
                            <th>M</th>
                            <th>S</th>
                            <th>K</th>
                            <th>Selisih Set</th>
                            <th>Selisih Poin</th>
                            <th>Poin</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($standings as $row)

                        @php
                        $setDiff =
                        $row['sets_for']
                        - $row['sets_against'];

                        $pointDiff =
                        $row['points_for']
                        - $row['points_against'];
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="tr-standings-name">
                                {{ $row['entry']->display_name }}
                            </td>

                            <td>{{ $row['played'] }}</td>
                            <td>{{ $row['wins'] }}</td>
                            <td>{{ $row['draws'] }}</td>
                            <td>{{ $row['losses'] }}</td>

                            <td>
                                {{ $setDiff > 0 ? '+' : '' }}{{ $setDiff }}
                            </td>

                            <td>
                                {{ $pointDiff > 0 ? '+' : '' }}{{ $pointDiff }}
                            </td>

                            <td>
                                <strong>
                                    {{ $row['league_points'] }}
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

        <section class="tr-panel">

            <div class="tr-section-head">
                <div>
                    <span class="tr-eyebrow">
                        MATCH CENTER
                    </span>
                    <h2>Pertandingan</h2>
                </div>

                <span class="tr-board-count">
                    {{ $totalPertandingan }} Pertandingan
                </span>
            </div>

            @if($errors->any())
            <div class="tr-alert" role="alert">
                <strong>Hasil belum disimpan.</strong>

                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif


            @if($totalRonde > 0)

            {{-- NAVIGASI RONDE --}}
            <nav class="tr-round-nav" aria-label="Navigasi ronde">

                @if($previousRound !== null)

                <a class="tr-round-arrow" aria-label="Ronde sebelumnya" href="{{ route('tournaments.show', [
                                'tournament' => $tournament,
                                'bagian' => 'pertandingan',
                                'ronde' => $previousRound,
                            ]) }}">
                    ‹
                </a>

                @else
                <span class="tr-round-arrow is-disabled">
                    ‹
                </span>
                @endif

                <h3>{{ $roundLabel }}</h3>

                @if($nextRound !== null)

                <a class="tr-round-arrow" aria-label="Ronde berikutnya" href="{{ route('tournaments.show', [
                                'tournament' => $tournament,
                                'bagian' => 'pertandingan',
                                'ronde' => $nextRound,
                            ]) }}">
                    ›
                </a>

                @else
                <span class="tr-round-arrow is-disabled">
                    ›
                </span>
                @endif

            </nav>


            {{-- KARTU DAN FORM PERTANDINGAN --}}
            <div class="tr-match-grid">

                @foreach($currentFixtures as $fixture)

                @php
                $ready = !$fixture->is_bye
                && $fixture->a_entry_id !== null
                && $fixture->b_entry_id !== null;

                $maxSets = $tournament->set_format
                === 'best_of_three' ? 3 : 2;

                $isOldFixture =
                (string) old('ui_fixture_id')
                === (string) $fixture->id;

                $openedFixture =
                (string) request()->query('edit')
                === (string) $fixture->id;
                @endphp

                <div class="tr-fixture-entry" id="fixture-{{ $fixture->id }}">

                    @include('tournaments._fixture', [
                    'fixture' => $fixture,
                    'canManage' => false,
                    ])

                    @if($canManage && $ready)

                    <details class="tr-inline-editor" @if( $openedFixture || ($isOldFixture && $errors->any())
                        )
                        open
                        @endif>

                        <summary>
                            {{ $fixture->result !== null
                            ? 'Edit Skor & Kok'
                            : 'Isi Skor & Kok' }}
                        </summary>

                        <form method="POST" class="tr-inline-editor__form" action="{{ route(
                                            'tournaments.fixtures.update',
                                            [$tournament, $fixture]
                                        ) }}" data-single-submit>

                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="ui_fixture_id" value="{{ $fixture->id }}">

                            {{-- SKOR PER SET --}}
                            @for($i = 0; $i < $maxSets; $i++) @php $set=$fixture->sets
                                ->firstWhere(
                                'set_number',
                                $i + 1
                                );

                                $scoreA = $isOldFixture
                                ? old(
                                'scores.' . $i . '.a',
                                $set?->a_score
                                )
                                : $set?->a_score;

                                $scoreB = $isOldFixture
                                ? old(
                                'scores.' . $i . '.b',
                                $set?->b_score
                                )
                                : $set?->b_score;
                                @endphp

                                <div class="tr-score-set">

                                    <strong>
                                        SET {{ $i + 1 }}
                                    </strong>

                                    <div class="tr-score-inputs">

                                        <div class="tr-field">
                                            <label for="s-{{ $fixture->id }}-{{ $i }}-a">
                                                Tim A
                                            </label>

                                            <input id="s-{{ $fixture->id }}-{{ $i }}-a" type="number"
                                                name="scores[{{ $i }}][a]" min="0" max="999" inputmode="numeric"
                                                placeholder="—" value="{{ $scoreA }}">
                                        </div>

                                        <span class="tr-score-colon">
                                            :
                                        </span>

                                        <div class="tr-field">
                                            <label for="s-{{ $fixture->id }}-{{ $i }}-b">
                                                Tim B
                                            </label>

                                            <input id="s-{{ $fixture->id }}-{{ $i }}-b" type="number"
                                                name="scores[{{ $i }}][b]" min="0" max="999" inputmode="numeric"
                                                placeholder="—" value="{{ $scoreB }}">
                                        </div>

                                    </div>

                                </div>

                                @endfor


                                {{-- JUS AKUMULASI --}}
                                @if($tournament->set_format === 'two_aggregate')

                                <div class="tr-score-set">

                                    <strong>
                                        JUS AKUMULASI
                                    </strong>

                                    <div class="tr-score-inputs">

                                        <div class="tr-field">
                                            <label for="jus-a-{{ $fixture->id }}">
                                                Jus A
                                            </label>

                                            <input id="jus-a-{{ $fixture->id }}" type="number" name="jus_a" min="0"
                                                max="999" inputmode="numeric"
                                                value="{{ $isOldFixture ? old('jus_a', $fixture->jus_a) : $fixture->jus_a }}">
                                        </div>

                                        <span class="tr-score-colon">
                                            :
                                        </span>

                                        <div class="tr-field">
                                            <label for="jus-b-{{ $fixture->id }}">
                                                Jus B
                                            </label>

                                            <input id="jus-b-{{ $fixture->id }}" type="number" name="jus_b" min="0"
                                                max="999" inputmode="numeric"
                                                value="{{ $isOldFixture ? old('jus_b', $fixture->jus_b) : $fixture->jus_b }}">
                                        </div>

                                    </div>

                                </div>

                                @endif


                                {{-- JUMLAH KOK --}}
                                <div class="tr-field">
                                    <label for="kok-{{ $fixture->id }}">
                                        Jumlah Kok
                                    </label>

                                    <input id="kok-{{ $fixture->id }}" type="number" name="shuttlecock_count" min="0"
                                        max="65535" inputmode="numeric" required
                                        value="{{ $isOldFixture ? old('shuttlecock_count', $fixture->shuttlecock_count) : $fixture->shuttlecock_count }}">
                                </div>


                                {{-- KONFIRMASI KOREKSI CUP --}}
                                @if(
                                !$isLiga
                                && $fixture->next_fixture_id
                                )

                                <label class="tr-checkbox">

                                    <input type="checkbox" name="confirm_reset" value="1" @checked( $isOldFixture &&
                                        old('confirm_reset') )>

                                    <span>
                                        Jika pemenang berubah,
                                        saya setuju hasil
                                        pertandingan lanjutan
                                        yang terdampak direset.
                                    </span>

                                </label>

                                @endif


                                <button type="submit" class="tr-btn">
                                    Simpan Hasil
                                </button>

                        </form>

                    </details>

                    @endif

                </div>

                @endforeach

            </div>

            @else

            <div class="tr-empty">

                <strong>
                    Belum ada pertandingan.
                </strong>

                @if($isDraft && $canManage)
                <a href="{{ route('tournaments.entries', $tournament) }}" class="tr-btn tr-btn--outline">
                    Lanjutkan Pembuatan
                </a>
                @endif

            </div>

            @endif

        </section>

        @endif

    </div>

    @endsection

    @push('scripts')
    <script src="{{ asset('assets/js/tournaments.js') }}?v=13" defer>
    </script>
    @endpush