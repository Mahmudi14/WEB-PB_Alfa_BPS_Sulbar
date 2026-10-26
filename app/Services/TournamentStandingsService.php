<?php

namespace App\Services;

use App\Models\Tournament;
use Illuminate\Support\Collection;

class TournamentStandingsService
{
    public static function matchPoints(
        Tournament $tournament,
        string $result
    ): array {
        $win = (int) $tournament->league_win_points;
        $draw = (int) $tournament->league_draw_points;
        $loss = (int) $tournament->league_loss_points;

        return match ($result) {
            'a' => [$win, $loss],
            'b' => [$loss, $win],
            'draw' => [$draw, $draw],

            default => throw new \InvalidArgumentException(
                'Hasil pertandingan Liga tidak valid.'
            ),
        };
    }

    public function forTournament(
        Tournament $tournament
    ): Collection {
        $tournament->loadMissing([
            'entries.players',
            'fixtures.sets',
        ]);

        $table = [];

        foreach ($tournament->entries as $entry) {
            $table[$entry->id] = [
                'entry' => $entry,
                'played' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'league_points' => 0,
                'sets_for' => 0,
                'sets_against' => 0,
                'points_for' => 0,
                'points_against' => 0,
            ];
        }

        foreach ($tournament->fixtures as $fixture) {
            if (
                $fixture->result === null
                || !$fixture->a_entry_id
                || !$fixture->b_entry_id
            ) {
                continue;
            }

            $a = $fixture->a_entry_id;
            $b = $fixture->b_entry_id;

            if (!isset($table[$a], $table[$b])) {
                continue;
            }

            $table[$a]['played']++;
            $table[$b]['played']++;

            foreach ($fixture->sets as $set) {
                $table[$a]['points_for'] += $set->a_score;
                $table[$a]['points_against'] += $set->b_score;

                $table[$b]['points_for'] += $set->b_score;
                $table[$b]['points_against'] += $set->a_score;

                $table[$a]['sets_for'] +=
                    (int) ($set->a_score > $set->b_score);

                $table[$a]['sets_against'] +=
                    (int) ($set->a_score < $set->b_score);

                $table[$b]['sets_for'] +=
                    (int) ($set->b_score > $set->a_score);

                $table[$b]['sets_against'] +=
                    (int) ($set->b_score < $set->a_score);
            }

            // Poin tambahan dari jus akumulasi
            $table[$a]['points_for'] += $fixture->jus_a ?? 0;
            $table[$a]['points_against'] += $fixture->jus_b ?? 0;

            $table[$b]['points_for'] += $fixture->jus_b ?? 0;
            $table[$b]['points_against'] += $fixture->jus_a ?? 0;

            // Poin klasemen: mendukung angka negatif
            [$pointsA, $pointsB] = self::matchPoints(
                $tournament,
                $fixture->result
            );

            $table[$a]['league_points'] += $pointsA;
            $table[$b]['league_points'] += $pointsB;

            if ($fixture->result === 'draw') {
                $table[$a]['draws']++;
                $table[$b]['draws']++;
            } else {
                $winner = $fixture->result === 'a' ? $a : $b;
                $loser = $fixture->result === 'a' ? $b : $a;

                $table[$winner]['wins']++;
                $table[$loser]['losses']++;
            }
        }

        return collect(array_values($table))
            ->sort(function ($a, $b) {
                foreach (['league_points', 'wins'] as $field) {
                    if ($a[$field] !== $b[$field]) {
                        return $b[$field] <=> $a[$field];
                    }
                }

                $setDiffA =
                    $a['sets_for'] - $a['sets_against'];

                $setDiffB =
                    $b['sets_for'] - $b['sets_against'];

                if ($setDiffA !== $setDiffB) {
                    return $setDiffB <=> $setDiffA;
                }

                $pointDiffA =
                    $a['points_for'] - $a['points_against'];

                $pointDiffB =
                    $b['points_for'] - $b['points_against'];

                return ($pointDiffB <=> $pointDiffA)
                    ?: strcmp(
                        $a['entry']->display_name,
                        $b['entry']->display_name
                    );
            })
            ->values();
    }
}
