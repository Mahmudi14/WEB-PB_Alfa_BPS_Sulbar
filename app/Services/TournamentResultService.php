<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentFixture;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TournamentResultService
{
    public function __construct(
        private readonly TournamentScoringService $scoring,
        private readonly ShuttlecockCashService $cash
    ) {}

    public function save(
        Tournament $tournament,
        TournamentFixture $fixture,
        array $data,
        int $actorId
    ): void {
        DB::transaction(function () use (
            $tournament,
            $fixture,
            $data,
            $actorId
        ) {
            $tournament = Tournament::whereKey($tournament->id)
                ->lockForUpdate()
                ->firstOrFail();

            $fixture = TournamentFixture::whereKey($fixture->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $fixture->tournament_id !== (int) $tournament->id
                || $tournament->status === 'draft'
            ) {
                abort(404);
            }

            if (
                $fixture->is_bye
                || !$fixture->a_entry_id
                || !$fixture->b_entry_id
            ) {
                throw ValidationException::withMessages([
                    'scores' =>
                    'Pertandingan belum memiliki dua peserta atau merupakan BYE.',
                ]);
            }

            // Data sebelum perubahan, termasuk hasil Cup
            // lanjutan yang mungkin ikut di-reset.
            $before = $this->cash->tournamentPairs(
                (int) $tournament->id
            );

            $outcome = $this->scoring->evaluate(
                $tournament,
                $data['scores'] ?? [],
                isset($data['jus_a']) && $data['jus_a'] !== ''
                    ? (int) $data['jus_a']
                    : null,
                isset($data['jus_b']) && $data['jus_b'] !== ''
                    ? (int) $data['jus_b']
                    : null
            );

            $newWinner = match ($outcome['winner_side']) {
                'a' => $fixture->a_entry_id,
                'b' => $fixture->b_entry_id,
                default => null,
            };

            if (
                $tournament->format === 'cup'
                && $outcome['result'] === 'draw'
            ) {
                throw ValidationException::withMessages([
                    'scores' => 'Pertandingan Cup harus memiliki pemenang.',
                ]);
            }

            if (
                $tournament->format === 'cup'
                && (int) $fixture->winner_entry_id !== (int) $newWinner
                && $fixture->next_fixture_id
            ) {
                $this->changeAdvancedParticipant(
                    $fixture,
                    $newWinner,
                    (bool) ($data['confirm_reset'] ?? false)
                );
            }

            $fixture->sets()->delete();

            foreach ($outcome['sets'] as $index => $set) {
                $fixture->sets()->create([
                    'set_number' => $index + 1,
                    'a_score' => $set['a'],
                    'b_score' => $set['b'],
                ]);
            }

            $fixture->update([
                'played_on' => $data['played_on'],
                'result' => $outcome['result'],
                'winner_entry_id' => $newWinner,
                'shuttlecock_count' =>
                (int) $data['shuttlecock_count'],
                'jus_a' => $outcome['jus_a'],
                'jus_b' => $outcome['jus_b'],
            ]);

            $isFinished = !$tournament->fixtures()
                ->whereNull('result')
                ->where('is_bye', false)
                ->exists();

            $tournament->update([
                'status' => $isFinished ? 'completed' : 'active',
            ]);

            // Hitung ulang kok, termasuk data yang
            // berkurang akibat koreksi atau reset.
            $after = $this->cash->tournamentPairs(
                (int) $tournament->id
            );

            $this->cash->syncPairs(
                array_merge($before, $after),
                $actorId
            );
        }, 3);
    }

    private function changeAdvancedParticipant(
        TournamentFixture $from,
        ?int $newEntryId,
        bool $allowReset
    ): void {
        $next = TournamentFixture::whereKey($from->next_fixture_id)
            ->lockForUpdate()
            ->firstOrFail();

        $column = $from->next_slot === 'a'
            ? 'a_entry_id'
            : 'b_entry_id';

        if ((int) $next->{$column} === (int) $newEntryId) {
            return;
        }

        if ($this->hasRecordedData($next) && !$allowReset) {
            throw ValidationException::withMessages([
                'confirm_reset' =>
                'Pemenang berubah. Konfirmasi reset hasil pertandingan lanjutan terlebih dahulu.',
            ]);
        }

        $this->discardOutcomeAndPropagation(
            $next,
            $allowReset
        );

        $next->{$column} = $newEntryId;
        $next->save();
    }

    private function discardOutcomeAndPropagation(
        TournamentFixture $fixture,
        bool $allowReset
    ): void {
        if (
            $fixture->winner_entry_id
            && $fixture->next_fixture_id
        ) {
            $next = TournamentFixture::whereKey(
                $fixture->next_fixture_id
            )
                ->lockForUpdate()
                ->firstOrFail();

            $column = $fixture->next_slot === 'a'
                ? 'a_entry_id'
                : 'b_entry_id';

            if ($this->hasRecordedData($next) && !$allowReset) {
                throw ValidationException::withMessages([
                    'confirm_reset' =>
                    'Ada hasil pertandingan lanjutan. Setujui reset hasil turunannya.',
                ]);
            }

            $this->discardOutcomeAndPropagation(
                $next,
                $allowReset
            );

            $next->{$column} = null;
            $next->save();
        }

        $fixture->sets()->delete();

        $fixture->update([
            'winner_entry_id' => null,
            'result' => null,
            'played_on' => null,
            'jus_a' => null,
            'jus_b' => null,
            'shuttlecock_count' => 0,
        ]);
    }

    private function hasRecordedData(
        TournamentFixture $fixture
    ): bool {
        return $fixture->result !== null
            || $fixture->sets()->exists()
            || $fixture->shuttlecock_count > 0;
    }
}
