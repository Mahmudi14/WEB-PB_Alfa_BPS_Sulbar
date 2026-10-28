<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Facades\DB;

class PlayerRankingService
{
    public function all()
    {
        $regular = CompletedMatchQuery::regular()
            ->join(
                'match_participants as mp',
                'mp.match_id',
                '=',
                'm.id'
            )
            ->whereNotNull('mp.player_id')
            ->whereIn('mp.team', ['a', 'b'])
            ->select('mp.player_id')
            ->selectRaw("
                CASE
                    WHEN m.result = 'seri' THEN 'draw'
                    WHEN m.result = mp.team THEN 'win'
                    ELSE 'loss'
                END AS outcome
            ");

        $tournamentA = CompletedMatchQuery::tournament()
            ->join('tournament_entry_players as tep', function ($join) {
                $join->on('tep.entry_id', '=', 'tf.a_entry_id')
                    ->on('tep.tournament_id', '=', 'tf.tournament_id');
            })
            ->select('tep.player_id')
            ->selectRaw("
                CASE
                    WHEN tf.result = 'draw' THEN 'draw'
                    WHEN tf.result = 'a' THEN 'win'
                    ELSE 'loss'
                END AS outcome
            ");

        $tournamentB = CompletedMatchQuery::tournament()
            ->join('tournament_entry_players as tep', function ($join) {
                $join->on('tep.entry_id', '=', 'tf.b_entry_id')
                    ->on('tep.tournament_id', '=', 'tf.tournament_id');
            })
            ->select('tep.player_id')
            ->selectRaw("
                CASE
                    WHEN tf.result = 'draw' THEN 'draw'
                    WHEN tf.result = 'b' THEN 'win'
                    ELSE 'loss'
                END AS outcome
            ");

        $all = $regular
            ->unionAll($tournamentA)
            ->unionAll($tournamentB);

        $stats = DB::query()
            ->fromSub($all, 'hasil')
            ->select('hasil.player_id')
            ->selectRaw('COUNT(*) AS played_count')
            ->selectRaw("
                SUM(CASE WHEN outcome = 'win'
                    THEN 1 ELSE 0 END) AS wins
            ")
            ->selectRaw("
                SUM(CASE WHEN outcome = 'draw'
                    THEN 1 ELSE 0 END) AS draws
            ")
            ->selectRaw("
                SUM(CASE WHEN outcome = 'loss'
                    THEN 1 ELSE 0 END) AS losses
            ")
            ->groupBy('hasil.player_id');

        $players = Player::query()
            ->with('user')
            ->leftJoinSub(
                $stats,
                'stats',
                fn($join) => $join->on(
                    'players.id',
                    '=',
                    'stats.player_id'
                )
            )
            ->select('players.*')
            ->selectRaw("
                COALESCE(stats.played_count, 0)
                AS played_count
            ")
            ->selectRaw("
                COALESCE(stats.wins, 0) AS wins
            ")
            ->selectRaw("
                COALESCE(stats.draws, 0) AS draws
            ")
            ->selectRaw("
                COALESCE(stats.losses, 0) AS losses
            ")
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

        $positions = ['L' => 0, 'P' => 0];

        foreach ($players as $player) {
            if (!array_key_exists($player->gender, $positions)) {
                continue;
            }

            $player->rank_position =
                ++$positions[$player->gender];
        }

        return $players;
    }
}
