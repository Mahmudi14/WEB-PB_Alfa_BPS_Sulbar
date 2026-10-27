@extends('layouts.app')

@section('title', 'Edit Skor - PB Alfa')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/matches.css') }}?v=7">

<link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}?v=16">
@endpush

@section('content')

@php
$backUrl = route('tournaments.show', [
'tournament' => $tournament,
'bagian' => 'pertandingan',
'ronde' => $fixture->round_number,
]);

$isThree = $tournament->set_format === 'best_of_three';
$isDecider = $tournament->set_format === 'two_aggregate';

$set1 = $fixture->sets->firstWhere('set_number', 1);
$set2 = $fixture->sets->firstWhere('set_number', 2);
$set3 = $fixture->sets->firstWhere('set_number', 3);

$originalWinner = '';

if ($fixture->winner_entry_id !== null) {
if (
(int) $fixture->winner_entry_id
=== (int) $fixture->a_entry_id
) {
$originalWinner = 'a';
} elseif (
(int) $fixture->winner_entry_id
=== (int) $fixture->b_entry_id
) {
$originalWinner = 'b';
}
}

// Periksa hasil di ronde berikutnya.
$nextFixture = $fixture->nextFixture;

$needsResetConfirmation =
$tournament->format === 'cup'
&& $nextFixture !== null
&& (
$nextFixture->result !== null
|| $nextFixture->shuttlecock_count > 0
|| $nextFixture->sets()->exists()
);
@endphp


<div class="match-form-page tr-tournament-score-edit">

    {{-- HEADER EDIT SKOR --}}
    <header class="tr-score-hero">

        <a href="{{ $backUrl }}" class="tr-score-hero__back">
            ← Kembali ke Pertandingan
        </a>

        <span class="tr-score-hero__eyebrow">
            PB ALFA / MATCH CENTER
        </span>

        <h1>
            EDIT <span>SKOR.</span>
        </h1>

    </header>

    {{-- FORM EDIT --}}
    <section class="match-form-card">


        @if($errors->any())
        <div class="match-alert" role="alert">
            <strong>Periksa skor pertandingan.</strong>

            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="tr-edit-score-form" method="POST" action="{{ route(
                'tournaments.fixtures.update',
                [$tournament, $fixture]
            ) }}" class="match-form" data-single-submit data-score-format="{{ $tournament->set_format }}"
            data-original-winner="{{ $originalWinner }}" data-confirm-reset="{{ $needsResetConfirmation ? '1' : '0' }}">

            @csrf
            @method('PATCH')

            <input type="hidden" name="confirm_reset" value="0">


            {{-- ===========================
            TIM A DAN TIM B
            =========================== --}}
            <section class="match-section">

                <div class="match-teams">

                    @foreach([
                    'a' => $fixture->entryA,
                    'b' => $fixture->entryB,
                    ] as $side => $entry)

                    @php
                    $players = $entry?->players
                    ?->sortBy(fn ($player) =>
                    $player->pivot?->slot ?? 0
                    ) ?? collect();
                    @endphp

                    <div class="match-team-column">

                        <div class="match-team-column__heading
                                {{ $side === 'b'
                                    ? 'match-team-column__heading--b'
                                    : '' }}">

                            Tim {{ strtoupper($side) }}

                        </div>

                        @forelse($players as $player)

                        <div class="tr-tournament-player-name">

                            <span>
                                {{ $player->name }}
                            </span>

                        </div>

                        @empty

                        <div class="tr-tournament-player-name">
                            Menunggu
                        </div>

                        @endforelse

                    </div>

                    @endforeach

                </div>

            </section>


            {{-- ===========================
            SKOR PERTANDINGAN
            =========================== --}}
            <section class="match-section">

                <div class="match-section__heading">
                    <h3>Skor Pertandingan</h3>
                </div>


                {{-- SET 1 / KOK / SET 2 --}}
                <div class="match-scoreboard">

                    {{-- SET 1 --}}
                    <div class="match-scoreboard__set">

                        <label>SET 1</label>

                        <div class="match-scoreboard__inputs">

                            <input type="number" name="scores[0][a]" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="A" aria-label="Set 1 Tim A" value="{{ old(
                                    'scores.0.a',
                                    $set1?->a_score
                                ) }}">

                            <span>:</span>

                            <input type="number" name="scores[0][b]" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="B" aria-label="Set 1 Tim B" value="{{ old(
                                    'scores.0.b',
                                    $set1?->b_score
                                ) }}">

                        </div>

                        <small>Tim A : Tim B</small>

                    </div>


                    {{-- KOK --}}
                    <div class="match-scoreboard__cock">

                        <label for="tr-kok">
                            KOK
                        </label>

                        <input id="tr-kok" type="number" name="shuttlecock_count" min="0" max="65535" step="1"
                            inputmode="numeric" required value="{{ old(
                                'shuttlecock_count',
                                $fixture->shuttlecock_count
                            ) }}">

                    </div>


                    {{-- SET 2 --}}
                    <div class="match-scoreboard__set">

                        <label>SET 2</label>

                        <div class="match-scoreboard__inputs">

                            <input type="number" name="scores[1][a]" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="A" aria-label="Set 2 Tim A" value="{{ old(
                                    'scores.1.a',
                                    $set2?->a_score
                                ) }}">

                            <span>:</span>

                            <input type="number" name="scores[1][b]" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="B" aria-label="Set 2 Tim B" value="{{ old(
                                    'scores.1.b',
                                    $set2?->b_score
                                ) }}">

                        </div>

                        <small>Tim A : Tim B</small>

                    </div>

                </div>


                {{-- SET 3 JIKA DIPERLUKAN --}}
                @if($isThree)

                <div class="tr-score-extra">

                    <div class="match-scoreboard__set">

                        <label>SET 3</label>

                        <div class="match-scoreboard__inputs">

                            <input type="number" name="scores[2][a]" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="A" aria-label="Set 3 Tim A" value="{{ old(
                                        'scores.2.a',
                                        $set3?->a_score
                                    ) }}">

                            <span>:</span>

                            <input type="number" name="scores[2][b]" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="B" aria-label="Set 3 Tim B" value="{{ old(
                                        'scores.2.b',
                                        $set3?->b_score
                                    ) }}">

                        </div>

                        <small>Tim A : Tim B</small>

                    </div>

                </div>

                @endif


                {{-- POIN PENENTU: hanya muncul jika total seri --}}
                @if($isDecider)

                <div class="tr-score-extra" data-score-decider>

                    <div class="match-scoreboard__set">

                        <label>POIN PENENTU</label>

                        <div class="match-scoreboard__inputs">

                            <input type="number" name="jus_a" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="A" aria-label="Poin penentu Tim A" value="{{ old(
                                        'jus_a',
                                        $fixture->jus_a
                                    ) }}">

                            <span>:</span>

                            <input type="number" name="jus_b" min="0" max="999" step="1" inputmode="numeric"
                                placeholder="B" aria-label="Poin penentu Tim B" value="{{ old(
                                        'jus_b',
                                        $fixture->jus_b
                                    ) }}">

                        </div>

                        <small>Tim A : Tim B</small>

                    </div>

                </div>

                @endif

            </section>


            {{-- TOMBOL SIMPAN --}}
            <div class="match-form__actions">

                <button type="submit" class="match-submit">
                    Simpan Perubahan
                </button>

                <a href="{{ $backUrl }}" class="match-cancel">
                    Batal
                </a>

            </div>

        </form>

    </section>

</div>

@endsection


@push('scripts')
<script>
    (function () {
    const form = document.getElementById('tr-edit-score-form');
    if (!form) return;

    const format = form.dataset.scoreFormat;
    const decider = form.querySelector('[data-score-decider]');

    const score = (set, side) =>
        form.elements.namedItem(`scores[${set}][${side}]`);

    function pair(set) {
        const a = score(set, 'a')?.value ?? '';
        const b = score(set, 'b')?.value ?? '';

        if (a === '' || b === '') return null;

        return [Number(a), Number(b)];
    }

    function updateDecider() {
        if (!decider) return;

        const first = pair(0);
        const second = pair(1);

        const needs = first !== null
            && second !== null
            && first[0] + second[0]
                === first[1] + second[1];

        decider.hidden = !needs;

        if (!needs) {
            decider.querySelectorAll('input').forEach(input => {
                input.value = '';
            });
        }
    }

    // Perlihatkan kolom penentu hanya ketika dibutuhkan.
    form.addEventListener('input', event => {
        if (event.target.name?.startsWith('scores[')) {
            updateDecider();
        }
    });

    updateDecider();

    // Tentukan apakah pemenang berubah.
    function newWinner() {
        const first = pair(0);
        const second = pair(1);

        if (!first || !second) return '';

        const winnerOf = values =>
            values[0] > values[1] ? 'a'
            : values[1] > values[0] ? 'b'
            : '';

        if (format === 'best_of_three') {
            const w1 = winnerOf(first);
            const w2 = winnerOf(second);

            if (w1 && w1 === w2) return w1;

            const third = pair(2);
            return third ? winnerOf(third) : '';
        }

        if (format === 'two_draw') {
            const w1 = winnerOf(first);
            const w2 = winnerOf(second);

            return w1 && w1 === w2 ? w1 : '';
        }

        const totalA = first[0] + second[0];
        const totalB = first[1] + second[1];

        if (totalA > totalB) return 'a';
        if (totalB > totalA) return 'b';

        const a = form.elements.namedItem('jus_a')?.value ?? '';
        const b = form.elements.namedItem('jus_b')?.value ?? '';

        if (a === '' || b === '') return '';

        return Number(a) > Number(b) ? 'a'
            : Number(b) > Number(a) ? 'b'
            : '';
    }

    let resetApproved = false;

    form.addEventListener('submit', function (event) {
        if (
            resetApproved
            || form.dataset.confirmReset !== '1'
            || newWinner() === form.dataset.originalWinner
        ) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const confirmText =
            'Mengubah pemenang akan mengosongkan hasil pertandingan lanjutan yang terdampak. Lanjutkan?';

        const proceed = () => {
            form.elements.namedItem('confirm_reset').value = '1';
            resetApproved = true;
            form.requestSubmit();
        };

        if (window.PBAlfaDialog?.confirm) {
            window.PBAlfaDialog.confirm(
                confirmText,
                proceed,
                {
                    title: 'Konfirmasi Perubahan Pemenang',
                    confirmText: 'Ya, Lanjutkan',
                }
            );
        } else if (window.confirm(confirmText)) {
            proceed();
        }
    }, true);
})();
</script>
@endpush