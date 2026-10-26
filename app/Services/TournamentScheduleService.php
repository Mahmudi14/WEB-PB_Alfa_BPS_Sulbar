<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentFixture;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TournamentScheduleService
{
    /** Pure deterministic round-robin: every unordered pair appears exactly once. */
    public static function leaguePlan(
        array $entries,
        int $legs = 1
    ): array {
        $pool = array_values($entries);

        // Peserta ganjil mendapatkan BYE.
        if (count($pool) % 2 === 1) {
            $pool[] = null;
        }

        $n = count($pool);
        $rounds = [];

        // Putaran pertama
        for ($round = 0; $round < $n - 1; $round++) {
            $matches = [];

            for ($i = 0; $i < $n / 2; $i++) {
                $a = $pool[$i];
                $b = $pool[$n - 1 - $i];

                if ($a !== null && $b !== null) {
                    $matches[] = [
                        'a' => $a,
                        'b' => $b,
                        'bye' => false,
                    ];
                }
            }

            $rounds[] = $matches;

            // Rotasi peserta untuk ronde berikutnya
            $pool = array_merge(
                [$pool[0], $pool[$n - 1]],
                array_slice($pool, 1, $n - 2)
            );
        }

        // Putaran kedua jika memilih 2 kali bertemu
        if ($legs === 2) {
            $firstLegRounds = $rounds;

            foreach ($firstLegRounds as $firstLegRound) {
                $returnRound = [];

                foreach ($firstLegRound as $fixture) {
                    $returnRound[] = [
                        'a' => $fixture['b'],
                        'b' => $fixture['a'],
                        'bye' => false,
                    ];
                }

                $rounds[] = $returnRound;
            }
        }

        return $rounds;
    }

    /**
     * Seeded Cup layout distributes first-round BYEs (e.g. 6 entrants in 8 slots).
     * Null positions are real BYEs, not matches entered by managers.
     */
    public static function cupPlan(array $entries): array
    {
        $n = count($entries);
        $size = 2;
        while ($size < $n) $size *= 2;
        // Manual order means: BYE recipients first, then direct first-round pairs.
        // Distribute BYEs across opposite bracket halves using bit-reversed pair indices.
        $pairCount = intdiv($size, 2);
        $bits = (int) round(log($pairCount, 2));
        $spread = [];
        for ($i = 0; $i < $pairCount; $i++) {
            $value = $i;
            $reversed = 0;
            for ($bit = 0; $bit < $bits; $bit++) {
                $reversed = ($reversed << 1) | ($value & 1);
                $value >>= 1;
            }
            $spread[] = $reversed;
        }
        $byePairs = array_flip(array_slice($spread, 0, $size - $n));
        $slots = [];
        $byeEntrants = array_slice($entries, 0, $size - $n);
        $remainingEntrants = array_slice($entries, $size - $n);
        $byeCursor = 0;
        $otherCursor = 0;
        for ($pair = 0; $pair < $pairCount; $pair++) {
            if (isset($byePairs[$pair])) {
                $slots[] = $byeEntrants[$byeCursor++];
                $slots[] = null;
            } else {
                $slots[] = $remainingEntrants[$otherCursor++];
                $slots[] = $remainingEntrants[$otherCursor++];
            }
        }
        $rounds = [];
        $first = [];
        for ($i = 0; $i < $size; $i += 2) {
            $a = $slots[$i];
            $b = $slots[$i + 1];
            $first[] = ['a' => $a, 'b' => $b, 'bye' => (($a === null) !== ($b === null))];
        }
        $rounds[] = $first;
        $matchCount = count($first);
        while ($matchCount > 1) {
            $matchCount /= 2;
            $rounds[] = array_fill(0, $matchCount, ['a' => null, 'b' => null, 'bye' => false]);
        }
        // Show entrants who advance by BYE in the preview.
        foreach ($first as $index => $fixture) {
            if (!$fixture['bye'] || !isset($rounds[1])) continue;
            $dest = intdiv($index, 2);
            $side = $index % 2 === 0 ? 'a' : 'b';
            $rounds[1][$dest][$side] = $fixture['a'] ?? $fixture['b'];
        }
        return $rounds;
    }

    public function preview(
        Tournament $tournament,
        array $entryIds
    ): array {
        return $tournament->format === 'liga'
            ? self::leaguePlan(
                $entryIds,
                (int) ($tournament->league_legs ?? 1)
            )
            : self::cupPlan($entryIds);
    }

    public function generate(Tournament $tournament, array $entryIds): void
    {
        DB::transaction(function () use ($tournament, $entryIds) {
            $tournament = Tournament::whereKey($tournament->id)->lockForUpdate()->firstOrFail();
            if ($tournament->status !== 'draft' || $tournament->fixtures()->exists()) {
                throw ValidationException::withMessages(['order' => 'Jadwal turnamen sudah ditetapkan dan tidak dapat digenerate ulang.']);
            }
            $actual = $tournament->entries()->pluck('id')->map(fn($id) => (int) $id)->all();
            if (count($actual) < 2 || count($actual) !== count($entryIds) || array_diff($actual, $entryIds) || count(array_unique($entryIds)) !== count($entryIds)) {
                throw ValidationException::withMessages(['order' => 'Daftar peserta berubah. Muat ulang halaman bagan.']);
            }
            if ($tournament->format === 'cup' && $tournament->set_format === 'two_draw') {
                throw ValidationException::withMessages(['set_format' => 'Cup tidak dapat menggunakan sistem yang memungkinkan seri.']);
            }

            foreach ($entryIds as $index => $entryId) {
                $tournament->entries()->whereKey($entryId)->update(['seed' => $index + 1]);
            }
            $plan = $this->preview($tournament, $entryIds);
            $created = [];
            foreach ($plan as $r => $round) {
                foreach ($round as $m => $fixture) {
                    $created[$r][$m] = $tournament->fixtures()->create([
                        'round_number' => $r + 1,
                        'match_number' => $m + 1,
                        'a_entry_id' => $fixture['a'],
                        'b_entry_id' => $fixture['b'],
                        'is_bye' => $tournament->format === 'cup' && $r === 0 && $fixture['bye'],
                    ]);
                }
            }
            if ($tournament->format === 'cup') {
                foreach ($created as $r => $round) {
                    if (!isset($created[$r + 1])) continue;
                    foreach ($round as $m => $fixture) {
                        $next = $created[$r + 1][intdiv($m, 2)];
                        $fixture->update(['next_fixture_id' => $next->id, 'next_slot' => $m % 2 === 0 ? 'a' : 'b']);
                        if ($fixture->is_bye) {
                            $winner = $fixture->a_entry_id ?? $fixture->b_entry_id;
                            $fixture->update(['winner_entry_id' => $winner]);
                            $next->update([$fixture->next_slot . '_entry_id' => $winner]);
                        }
                    }
                }
            }
            $tournament->update(['status' => 'active']);
        }, 3);
    }
}
