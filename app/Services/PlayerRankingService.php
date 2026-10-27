<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Facades\DB;

class PlayerRankingService
{
    public function all()
    {
        /*
        |--------------------------------------------------------------------------
        | PERTANDINGAN REGULER
        |--------------------------------------------------------------------------
        |
        | Menang = win
        | Seri   = draw
        | Kalah  = loss
        |
        | Hanya pertandingan selesai yang dihitung.
        |
        */

        $regularResults = DB::table('match_participants as mp')
            ->join(
                'matches as m',
                'm.id',
                '=',
                'mp.match_id'
            )
            ->whereNull('m.deleted_at')
            ->whereIn('m.result', ['a', 'b', 'seri'])
            ->whereNotNull('m.set1_a')
            ->whereNotNull('m.set1_b')
            ->whereNotNull('m.set2_a')
            ->whereNotNull('m.set2_b')
            ->whereNotNull('mp.player_id')
            ->whereIn('mp.team', ['a', 'b'])

            ->select('mp.player_id')

            ->selectRaw("
                CASE
                    WHEN m.result = 'seri'
                        THEN 'draw'
                    WHEN m.result = mp.team
                        THEN 'win'
                    ELSE 'loss'
                END AS outcome
            ");


        /*
        |--------------------------------------------------------------------------
        | PERTANDINGAN TURNAMEN - TIM A
        |--------------------------------------------------------------------------
        |
        | Berlaku untuk Liga dan Cup.
        | BYE dan pertandingan pending diabaikan.
        |
        */

        $tournamentResultsA = DB::table(
            'tournament_entry_players as tep'
        )
            ->join(
                'tournament_fixtures as tf',
                'tf.a_entry_id',
                '=',
                'tep.entry_id'
            )
            ->join(
                'tournaments as t',
                't.id',
                '=',
                'tf.tournament_id'
            )
            ->whereColumn(
                'tep.tournament_id',
                'tf.tournament_id'
            )
            ->whereIn(
                't.status',
                ['active', 'completed']
            )
            ->where('tf.is_bye', false)
            ->whereNotNull('tf.a_entry_id')
            ->whereNotNull('tf.b_entry_id')
            ->whereIn(
                'tf.result',
                ['a', 'b', 'draw']
            )

            ->select('tep.player_id')

            ->selectRaw("
                CASE
                    WHEN tf.result = 'draw'
                        THEN 'draw'
                    WHEN tf.result = 'a'
                        THEN 'win'
                    ELSE 'loss'
                END AS outcome
            ");


        /*
        |--------------------------------------------------------------------------
        | PERTANDINGAN TURNAMEN - TIM B
        |--------------------------------------------------------------------------
        */

        $tournamentResultsB = DB::table(
            'tournament_entry_players as tep'
        )
            ->join(
                'tournament_fixtures as tf',
                'tf.b_entry_id',
                '=',
                'tep.entry_id'
            )
            ->join(
                'tournaments as t',
                't.id',
                '=',
                'tf.tournament_id'
            )
            ->whereColumn(
                'tep.tournament_id',
                'tf.tournament_id'
            )
            ->whereIn(
                't.status',
                ['active', 'completed']
            )
            ->where('tf.is_bye', false)
            ->whereNotNull('tf.a_entry_id')
            ->whereNotNull('tf.b_entry_id')
            ->whereIn(
                'tf.result',
                ['a', 'b', 'draw']
            )

            ->select('tep.player_id')

            ->selectRaw("
                CASE
                    WHEN tf.result = 'draw'
                        THEN 'draw'
                    WHEN tf.result = 'b'
                        THEN 'win'
                    ELSE 'loss'
                END AS outcome
            ");


        /*
        |--------------------------------------------------------------------------
        | GABUNGKAN REGULER + LIGA + CUP
        |--------------------------------------------------------------------------
        |
        | UNION ALL menggabungkan hasil tanpa
        | memindahkan atau menduplikasi pertandingan
        | ke tabel matches.
        |
        */

        $allResults = $regularResults
            ->unionAll($tournamentResultsA)
            ->unionAll($tournamentResultsB);


        /*
        |--------------------------------------------------------------------------
        | HITUNG STATISTIK SETIAP PEMAIN
        |--------------------------------------------------------------------------
        */

        $stats = DB::query()
            ->fromSub($allResults, 'hasil')

            ->select('hasil.player_id')

            ->selectRaw("
                COUNT(*) AS played_count
            ")

            ->selectRaw("
                SUM(
                    CASE
                        WHEN hasil.outcome = 'win'
                        THEN 1
                        ELSE 0
                    END
                ) AS wins
            ")

            ->selectRaw("
                SUM(
                    CASE
                        WHEN hasil.outcome = 'draw'
                        THEN 1
                        ELSE 0
                    END
                ) AS draws
            ")

            ->selectRaw("
                SUM(
                    CASE
                        WHEN hasil.outcome = 'loss'
                        THEN 1
                        ELSE 0
                    END
                ) AS losses
            ")

            ->groupBy('hasil.player_id');


        /*
        |--------------------------------------------------------------------------
        | HITUNG POIN RANKING GLOBAL
        |--------------------------------------------------------------------------
        |
        | Menang +30
        | Seri   +10
        | Kalah  -30
        |
        | Skor awal tetap digunakan.
        |
        */

        $players = Player::query()

            ->with('user')

            ->leftJoinSub(
                $stats,
                'stats',
                function ($join) {
                    $join->on(
                        'players.id',
                        '=',
                        'stats.player_id'
                    );
                }
            )

            ->select('players.*')

            ->selectRaw("
                COALESCE(stats.played_count, 0)
                AS played_count
            ")

            ->selectRaw("
                COALESCE(stats.wins, 0)
                AS wins
            ")

            ->selectRaw("
                COALESCE(stats.draws, 0)
                AS draws
            ")

            ->selectRaw("
                COALESCE(stats.losses, 0)
                AS losses
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


        /*
        |--------------------------------------------------------------------------
        | RANKING PUTRA DAN PUTRI
        |--------------------------------------------------------------------------
        */

        $positions = [
            'L' => 0,
            'P' => 0,
        ];

        foreach ($players as $player) {

            $gender = $player->gender;

            if (!array_key_exists($gender, $positions)) {
                continue;
            }

            $positions[$gender]++;

            $player->rank_position =
                $positions[$gender];
        }

        return $players;
    }
}
