@php
    $aName = $fixture->entryA?->display_name ?? ($fixture->is_bye ? 'BYE' : 'Menunggu peserta');
    $bName = $fixture->entryB?->display_name ?? ($fixture->is_bye ? 'BYE' : 'Menunggu peserta');
    $ready = !$fixture->is_bye && $fixture->a_entry_id && $fixture->b_entry_id;
    $isDraw = $fixture->is_completed && $fixture->result === 'draw';
    $aWinner = $fixture->is_completed && $fixture->winner_entry_id && $fixture->winner_entry_id === $fixture->a_entry_id;
    $bWinner = $fixture->is_completed && $fixture->winner_entry_id && $fixture->winner_entry_id === $fixture->b_entry_id;
    $aClass = $isDraw ? 'is-draw' : ($aWinner ? 'is-winner' : ($bWinner ? 'is-loser' : (!$fixture->is_completed && $ready ? 'is-pending' : '')));
    $bClass = $isDraw ? 'is-draw' : ($bWinner ? 'is-winner' : ($aWinner ? 'is-loser' : (!$fixture->is_completed && $ready ? 'is-pending' : '')));
    $scoreText = $fixture->sets->map(fn ($set) => $set->a_score.' : '.$set->b_score)->implode('  ·  ');
@endphp
<article class="tr-match {{ $fixture->is_completed ? 'is-finished' : '' }}">
    <div class="tr-match-top">
        <span>PERTANDINGAN {{ $fixture->match_number }}</span>
        <span class="tr-badge {{ $isDraw ? 'tr-badge--draw' : ($fixture->is_completed ? 'tr-badge--completed' : 'tr-badge--draft') }}">
            {{ $fixture->is_bye ? 'BYE' : ($fixture->is_completed ? ($isDraw ? 'Seri' : 'Selesai') : ($ready ? 'Belum dimainkan' : 'Menunggu peserta')) }}
        </span>
    </div>

    <div class="tr-match-sides">
        <div class="tr-team {{ $aClass }}">
            <small>TIM A</small>
            <strong>{{ $aName }}</strong>
        </div>
        <div class="tr-match-center">
            <span class="tr-versus">VS</span>
            <span class="tr-match-kok" aria-label="Jumlah kok {{ $fixture->shuttlecock_count }}">
                <small>KOK</small>
                <strong>{{ $fixture->shuttlecock_count }}</strong>
            </span>
        </div>
        <div class="tr-team tr-team--b {{ $bClass }}">
            <small>TIM B</small>
            <strong>{{ $bName }}</strong>
        </div>
    </div>

    <div class="tr-match-bottom">
        <div class="tr-match-scores">
            <span>{{ $scoreText ?: 'Skor belum tersedia' }}</span>
            @if($fixture->jus_a !== null && $fixture->jus_b !== null)
                <small>Jus: {{ $fixture->jus_a }} : {{ $fixture->jus_b }}</small>
            @elseif($scoreText)
                <small>Skor per set</small>
            @endif
        </div>
        @if($canManage && $ready)
            <a class="tr-btn tr-btn--small tr-btn--outline" href="{{ route('tournaments.fixtures.edit', [$tournament, $fixture]) }}">
                Edit Skor &amp; Kok
            </a>
        @endif
    </div>
</article>
