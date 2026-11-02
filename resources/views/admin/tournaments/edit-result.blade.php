@extends('layouts.admin')

@section('title', 'Edit Skor Turnamen - Admin PB Alfa')
@section('page_title', 'Edit Skor Turnamen')

@section('content')

@php
$sets = $fixture->sets->keyBy('set_number');

$isThree = $tournament->set_format === 'best_of_three';
$isAggregate = $tournament->set_format === 'two_aggregate';

$originalWinner = '';

if ((int) $fixture->winner_entry_id === (int) $fixture->a_entry_id
&& $fixture->winner_entry_id !== null) {
$originalWinner = 'a';
} elseif (
(int) $fixture->winner_entry_id === (int) $fixture->b_entry_id
&& $fixture->winner_entry_id !== null
) {
$originalWinner = 'b';
}

$next = $fixture->nextFixture;

$needsResetConfirmation =
$tournament->format === 'cup'
&& $next !== null
&& (
$next->result !== null
|| $next->shuttlecock_count > 0
|| $next->sets()->exists()
);
@endphp

<div class="adm-page adm-match-page">

    <div class="adm-page__heading">
        <div>
            <span class="adm-eyebrow">
                TOURNAMENT MATCH / EDIT RESULT
            </span>

            <h2>Edit Skor Turnamen</h2>

            <p>
                {{ $tournament->name }} —
                {{ $tournament->set_format_label }}
            </p>
        </div>

        <a href="{{ route('admin.matches.index') }}" class="adm-btn adm-btn--secondary">
            Kembali
        </a>
    </div>

    <section class="adm-card adm-match-form-card">

        <div class="adm-match-teams">

            @foreach([
            'a' => $fixture->entryA,
            'b' => $fixture->entryB
            ] as $side => $entry)

            <div class="adm-match-team">

                <div class="adm-match-team__heading">
                    Tim {{ strtoupper($side) }}
                </div>

                <div class="adm-match-team__players">
                    {{ $entry?->players
                    ?->sortBy(fn ($p) => $p->pivot?->slot ?? 0)
                    ?->pluck('name')
                    ?->implode(' / ') ?: 'Menunggu' }}
                </div>

            </div>

            @endforeach

        </div>

    </section>

    <form method="POST" action="{{ route(
            'admin.tournaments.fixtures.update',
            [$tournament, $fixture]
        ) }}" data-single-submit data-admin-tournament-form data-score-format="{{ $tournament->set_format }}"
        data-original-winner="{{ $originalWinner }}" data-confirm-reset="{{ $needsResetConfirmation ? '1' : '0' }}">
        @csrf
        @method('PATCH')

        <input type="hidden" name="confirm_reset" value="0">

        <section class="adm-card adm-match-form-card">

            <div class="adm-player-section-title">
                <h3>Informasi Pertandingan</h3>
                <p>Tanggal dan pemakaian kok.</p>
            </div>

            <div class="adm-match-form-grid">

                <div class="adm-field">
                    <label for="tournament-match-date">
                        Tanggal Bermain *
                    </label>

                    <input id="tournament-match-date" type="date" name="played_on" class="adm-input" value="{{ old(
                            'played_on',
                            $fixture->played_on?->format('Y-m-d')
                                ?? now()->toDateString()
                        ) }}" required>
                </div>

                <div class="adm-field">
                    <label for="tournament-match-cock">
                        Jumlah Kok *
                    </label>

                    <input id="tournament-match-cock" type="number" name="shuttlecock_count" class="adm-input" min="0"
                        max="65535" step="1" inputmode="numeric" value="{{ old(
                            'shuttlecock_count',
                            $fixture->shuttlecock_count
                        ) }}" required>
                </div>

            </div>

        </section>


        <section class="adm-card adm-match-form-card">

            <div class="adm-player-section-title">
                <h3>Skor Pertandingan</h3>

                <p>
                    Target {{ $tournament->target_points }} poin.
                    Ketentuan pemenang mengikuti aturan turnamen.
                </p>
            </div>

            <div class="adm-match-score-grid">

                @foreach(range(1, $isThree ? 3 : 2) as $number)

                <div class="adm-match-score-box">

                    <span class="adm-match-score-box__title">
                        SET {{ $number }}
                    </span>

                    <div class="adm-match-score-box__inputs">

                        <input type="number" name="scores[{{ $number - 1 }}][a]" min="0" max="999" step="1"
                            inputmode="numeric" placeholder="A" aria-label="Set {{ $number }} Tim A" value="{{ old(
                                    'scores.' . ($number - 1) . '.a',
                                    $sets->get($number)?->a_score
                                ) }}">

                        <span>:</span>

                        <input type="number" name="scores[{{ $number - 1 }}][b]" min="0" max="999" step="1"
                            inputmode="numeric" placeholder="B" aria-label="Set {{ $number }} Tim B" value="{{ old(
                                    'scores.' . ($number - 1) . '.b',
                                    $sets->get($number)?->b_score
                                ) }}">

                    </div>

                    <small>Tim A : Tim B</small>

                </div>

                @endforeach

            </div>


            @if($isAggregate)

            <div class="adm-match-decider" data-admin-score-decider>

                <h4>Poin Penentu</h4>

                <p>
                    Digunakan hanya apabila total poin
                    kedua set sama.
                </p>

                <div class="adm-match-score-box">

                    <div class="adm-match-score-box__inputs">

                        <input type="number" name="jus_a" min="0" max="999" step="1" inputmode="numeric" placeholder="A"
                            aria-label="Poin Penentu Tim A" value="{{ old('jus_a', $fixture->jus_a) }}">

                        <span>:</span>

                        <input type="number" name="jus_b" min="0" max="999" step="1" inputmode="numeric" placeholder="B"
                            aria-label="Poin Penentu Tim B" value="{{ old('jus_b', $fixture->jus_b) }}">

                    </div>

                </div>

            </div>

            @endif

            <div class="adm-match-preview" data-tournament-result-preview>
                Hasil mengikuti ketentuan turnamen.
            </div>

            <div class="adm-match-form-actions">

                <a href="{{ route('admin.matches.index') }}" class="adm-btn adm-btn--secondary">
                    Batal
                </a>

                <button type="submit" class="adm-btn adm-btn--primary">
                    Simpan Skor & Kok
                </button>

            </div>

        </section>

    </form>

</div>

@endsection