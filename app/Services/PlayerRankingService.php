<?php

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Facades\DB;

class PlayerRankingService
{
    public function all()
    {
        /*
         * Statistik hanya menghitung pertandingan
         * yang sudah mempunyai hasil lengkap.
         *
         * Pertandingan pending tidak menambah:
         * - Jumlah pertandingan
         * - Menang
         * - Seri
         * - Kalah
         * - Total poin
         */

        $stats = DB::table('match_participants as mp')
            ->join(
                'matches as m',
                'm.id',
                '=',
                'mp.match_id'
            )

            // Abaikan pertandingan terhapus
            ->whereNull('m.deleted_at')

            // Abaikan pertandingan tanpa hasil
            ->whereNotNull('m.result')

            // Semua skor wajib tersedia
            ->whereNotNull('m.set1_a')
            ->whereNotNull('m.set1_b')
            ->whereNotNull('m.set2_a')
            ->whereNotNull('m.set2_b')

            // Abaikan pemain luar yang tidak terdaftar
            ->whereNotNull('mp.player_id')

            ->select('mp.player_id')

            // Total pertandingan selesai
            ->selectRaw('COUNT(*) AS played_count')

            // Total kemenangan
            ->selectRaw("
                SUM(
                    CASE
                        WHEN
                            (
                                m.result = 'a'
                                AND mp.team = 'a'
                            )
                            OR
                            (
                                m.result = 'b'
                                AND mp.team = 'b'
                            )
                        THEN 1
                        ELSE 0
                    END
                ) AS wins
            ")

            // Total hasil seri
            ->selectRaw("
                SUM(
                    CASE
                        WHEN m.result = 'seri'
                        THEN 1
                        ELSE 0
                    END
                ) AS draws
            ")

            // Total kekalahan
            ->selectRaw("
                SUM(
                    CASE
                        WHEN
                            (
                                m.result = 'a'
                                AND mp.team = 'b'
                            )
                            OR
                            (
                                m.result = 'b'
                                AND mp.team = 'a'
                            )
                        THEN 1
                        ELSE 0
                    END
                ) AS losses
            ")

            ->groupBy('mp.player_id');

        /*
         * Ambil seluruh pemain beserta
         * hasil pertandingan yang sudah selesai.
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

            // Total pertandingan
            ->selectRaw("
                COALESCE(stats.played_count, 0)
                AS played_count
            ")

            // Total kemenangan
            ->selectRaw("
                COALESCE(stats.wins, 0)
                AS wins
            ")

            // Total seri
            ->selectRaw("
                COALESCE(stats.draws, 0)
                AS draws
            ")

            // Total kekalahan
            ->selectRaw("
                COALESCE(stats.losses, 0)
                AS losses
            ")

            /*
             * Perhitungan poin ranking:
             *
             * Menang = +30
             * Seri   = +10
             * Kalah  = -30
             *
             * Skor awal tetap diperhitungkan.
             */

            ->selectRaw("
                (
                    players.initial_score
                    + COALESCE(stats.wins, 0) * 30
                    + COALESCE(stats.draws, 0) * 10
                    - COALESCE(stats.losses, 0) * 30
                ) AS total_score
            ")

            // Urutan ranking
            ->orderByDesc('total_score')
            ->orderByDesc('wins')
            ->orderByDesc('draws')
            ->orderBy('losses')
            ->orderBy('players.name')
            ->orderBy('players.id')

            ->get();

        /*
         * Ranking Putra dan Putri
         * dihitung secara terpisah.
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

            $player->rank_position = $positions[$gender];
        }

        return $players;
    }
}
