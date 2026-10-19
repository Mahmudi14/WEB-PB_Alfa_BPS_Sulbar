<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Facades\DB;

class PlayerRankingService
{
    public function all()
    {
        $stats = DB::table('match_participants as mp')
            ->join('matches as m', 'm.id', '=', 'mp.match_id')
            ->whereNull('m.deleted_at')
            ->whereNotNull('mp.player_id')
            ->select('mp.player_id')
            ->selectRaw('COUNT(*) AS played_count')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN
                            (m.result = 'a' AND mp.team = 'a')
                            OR
                            (m.result = 'b' AND mp.team = 'b')
                        THEN 1 ELSE 0
                    END
                ) AS wins
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN m.result = 'seri'
                    THEN 1 ELSE 0 END
                ) AS draws
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN
                            (m.result = 'a' AND mp.team = 'b')
                            OR
                            (m.result = 'b' AND mp.team = 'a')
                        THEN 1 ELSE 0
                    END
                ) AS losses
            ")
            ->groupBy('mp.player_id');

        $players = Player::query()
            ->with('user')
            ->leftJoinSub($stats, 'stats', function ($join) {
                $join->on(
                    'players.id',
                    '=',
                    'stats.player_id'
                );
            })
            ->select('players.*')
            ->selectRaw('COALESCE(stats.played_count, 0) AS played_count')
            ->selectRaw('COALESCE(stats.wins, 0) AS wins')
            ->selectRaw('COALESCE(stats.draws, 0) AS draws')
            ->selectRaw('COALESCE(stats.losses, 0) AS losses')
            ->selectRaw("
                (
                    players.initial_score
                    + COALESCE(stats.wins, 0) * 30
                    + COALESCE(stats.draws, 0) * 10
                    - COALESCE(stats.losses, 0) * 30
                ) AS total_score
            ")
            ->orderByDesc('total_score')
->orderByDesc('wins')
->orderByDesc('draws')
->orderBy('losses')
->orderBy('players.name')
->orderBy('players.id')
->get();

        // Posisi ranking masing-masing kategori
        $positions = [
            'L' => 0,
            'P' => 0,
        ];

        $currentRanks = [
            'L' => 0,
            'P' => 0,
        ];

        $previousKeys = [
            'L' => null,
            'P' => null,
        ];

        foreach ($players as $player) {

            $gender = $player->gender;

            // Penomoran rank berbeda antara putra dan putri
            $positions[$gender]++;

            $key = [
                (int) $player->total_score,
                (int) $player->wins,
                (int) $player->losses,
            ];

            // Pemain dengan hasil identik berbagi peringkat
            if ($previousKeys[$gender] !== $key) {
                $currentRanks[$gender] = $positions[$gender];
            }

            $player->rank_position = $currentRanks[$gender];
            $previousKeys[$gender] = $key;
        }

        return $players;
    }
}
