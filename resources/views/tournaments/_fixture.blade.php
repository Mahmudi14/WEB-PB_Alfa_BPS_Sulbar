@php
$isBye = (bool) $fixture->is_bye;

$aId = $fixture->a_entry_id;
$bId = $fixture->b_entry_id;
$winnerId = $fixture->winner_entry_id;

$teamA = $fixture->entryA?->display_name
?? ($isBye ? 'BYE' : 'Menunggu');

$teamB = $fixture->entryB?->display_name
?? ($isBye ? 'BYE' : 'Menunggu');

$isDraw = !$isBye && $fixture->result === 'draw';

$isPending = !$isBye
&& $fixture->result === null;

// BYE tetap mempunyai pemenang otomatis.
$winnerA = (
$winnerId !== null
&& $aId !== null
&& (int) $winnerId === (int) $aId
) || (
$isBye
&& $aId !== null
&& $bId === null
);

$winnerB = (
$winnerId !== null
&& $bId !== null
&& (int) $winnerId === (int) $bId
) || (
$isBye
&& $bId !== null
&& $aId === null
);

$statusA = $isBye
? ($winnerA ? 'is-winner' : 'is-loser')
: ($isPending
? 'is-pending'
: ($isDraw
? 'is-draw'
: ($winnerA ? 'is-winner' : 'is-loser')));

$statusB = $isBye
? ($winnerB ? 'is-winner' : 'is-loser')
: ($isPending
? 'is-pending'
: ($isDraw
? 'is-draw'
: ($winnerB ? 'is-winner' : 'is-loser')));

$nameClassA = mb_strlen($teamA) > 28
? 'match-team--very-long'
: (mb_strlen($teamA) > 17
? 'match-team--long'
: '');

$nameClassB = mb_strlen($teamB) > 28
? 'match-team--very-long'
: (mb_strlen($teamB) > 17
? 'match-team--long'
: '');

$set1 = $fixture->sets->firstWhere('set_number', 1);
$set2 = $fixture->sets->firstWhere('set_number', 2);
$set3 = $fixture->sets->firstWhere('set_number', 3);

$scoreText = fn ($set) => $set
? $set->a_score . ' : ' . $set->b_score
: '— : —';

$ready = !$isBye
&& $aId !== null
&& $bId !== null;

$canOpenEditor = $canManage && $ready;

$hasThirdSet = $tournament->set_format === 'best_of_three';

$hasJus = $fixture->jus_a !== null
&& $fixture->jus_b !== null;
@endphp

<article class="match-row
    {{ $canOpenEditor ? 'match-row--manage' : '' }}
    {{ $isPending ? 'match-row--pending' : '' }}">

    <div class="match-row__teams">

        {{-- TIM A --}}
        <div class="match-side match-side--a">

            <div class="match-team {{ $statusA }} {{ $nameClassA }}">

                <span class="match-team__label">
                    TIM A
                </span>

                <strong>{{ $teamA }}</strong>

            </div>

            <div class="match-set">
                <small>SET 1</small>
                <strong>{{ $scoreText($set1) }}</strong>
            </div>

        </div>

        {{-- VS DAN KOK --}}
        <div class="match-center">

            <span class="match-row__vs">
                VS
            </span>

            <div class="match-cock" aria-label="Jumlah kok {{ $fixture->shuttlecock_count }}">

                <small>KOK</small>

                <strong>
                    {{ $fixture->shuttlecock_count }}
                </strong>

            </div>

        </div>

        {{-- TIM B --}}
        <div class="match-side match-side--b">

            <div class="match-team {{ $statusB }} {{ $nameClassB }}">

                <span class="match-team__label">
                    TIM B
                </span>

                <strong>{{ $teamB }}</strong>

            </div>

            <div class="match-set">
                <small>SET 2</small>
                <strong>{{ $scoreText($set2) }}</strong>
            </div>

        </div>

    </div>

    {{-- TOMBOL EDIT --}}
    @if($canOpenEditor)

    <a href="{{ route(
                'tournaments.fixtures.edit',
                [$tournament, $fixture]
            ) }}" class="match-edit" aria-label="Edit skor pertandingan">

        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true">

            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z" />

        </svg>

        <span>Edit</span>

    </a>

    @endif

    {{-- SET 3 / POIN PENENTU --}}
    @if($hasThirdSet || $hasJus)

    <div class="tr-tournament-match-extra">

        @if($hasThirdSet)
        <div class="tr-tournament-match-extra__item">
            <small>SET 3</small>
            <strong>{{ $scoreText($set3) }}</strong>
        </div>
        @endif

        @if($hasJus)
        <div class="tr-tournament-match-extra__item">
            <small>POIN PENENTU</small>
            <strong>
                {{ $fixture->jus_a }}
                :
                {{ $fixture->jus_b }}
            </strong>
        </div>
        @endif

    </div>

    @endif

</article>