<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class CompletedMatchQuery
{
    /*
    |--------------------------------------------------------------------------
    | PERTANDINGAN REGULER YANG SELESAI
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk Ranking Global dan Kas Kok.
    |
    | Pertandingan pending boleh tampil di
    | Daftar Pertandingan, tetapi tidak masuk
    | perhitungan Ranking maupun Kas.
    |
    */

    public static function regular(): Builder
    {
        return DB::table('matches as m')
            ->whereNull('m.deleted_at')

            ->whereIn(
                'm.result',
                ['a', 'b', 'seri']
            )

            ->whereNotNull('m.played_on')

            ->whereNotNull('m.set1_a')
            ->whereNotNull('m.set1_b')

            ->whereNotNull('m.set2_a')
            ->whereNotNull('m.set2_b')

            // Minimal ada peserta Tim A.
            ->whereExists(
                fn($q) => $q
                    ->selectRaw('1')
                    ->from('match_participants as pa')
                    ->whereColumn(
                        'pa.match_id',
                        'm.id'
                    )
                    ->where('pa.team', 'a')
            )

            // Minimal ada peserta Tim B.
            ->whereExists(
                fn($q) => $q
                    ->selectRaw('1')
                    ->from('match_participants as pb')
                    ->whereColumn(
                        'pb.match_id',
                        'm.id'
                    )
                    ->where('pb.team', 'b')
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PERTANDINGAN LIGA DAN CUP YANG VALID
    |--------------------------------------------------------------------------
    |
    | Tidak mengubah aturan skor asli.
    |
    | Hasil valid:
    | - Bukan BYE
    | - Memiliki dua peserta
    | - Memiliki tanggal
    | - Sudah ada hasil
    | - Set 1 dan Set 2 tersedia
    |
    | Best of Three:
    | - Menang 2-0: cukup dua set
    | - Imbang 1-1: wajib Set 3
    |
    */

    public static function tournament(): Builder
    {
        return DB::table('tournament_fixtures as tf')

            ->join(
                'tournaments as t',
                't.id',
                '=',
                'tf.tournament_id'
            )

            ->whereIn(
                't.format',
                ['liga', 'cup']
            )

            ->whereIn(
                't.status',
                ['active', 'completed']
            )

            ->where('tf.is_bye', false)

            ->whereNotNull('tf.played_on')

            ->whereNotNull('tf.a_entry_id')
            ->whereNotNull('tf.b_entry_id')

            ->whereIn(
                'tf.result',
                ['a', 'b', 'draw']
            )


            /*
             * SET 1 WAJIB ADA
             */

            ->whereExists(
                fn($q) => $q
                    ->selectRaw('1')
                    ->from('tournament_fixture_sets as fs1')
                    ->whereColumn(
                        'fs1.fixture_id',
                        'tf.id'
                    )
                    ->where('fs1.set_number', 1)
            )


            /*
             * SET 2 WAJIB ADA
             */

            ->whereExists(
                fn($q) => $q
                    ->selectRaw('1')
                    ->from('tournament_fixture_sets as fs2')
                    ->whereColumn(
                        'fs2.fixture_id',
                        'tf.id'
                    )
                    ->where('fs2.set_number', 2)
            )


            /*
             * VALIDASI BEST OF THREE
             *
             * Format selain Best of Three
             * tetap memakai pemeriksaan dua set.
             */

            ->where(function (Builder $q) {

                $q->where(
                    't.set_format',
                    '!=',
                    'best_of_three'
                )

                    ->orWhereExists(
                        function (Builder $bestOfThree) {

                            /*
                         * Bandingkan hasil Set 1
                         * dan Set 2 pada fixture sama.
                         */

                            $bestOfThree
                                ->selectRaw('1')

                                ->from(
                                    'tournament_fixture_sets as s1'
                                )

                                ->join(
                                    'tournament_fixture_sets as s2',
                                    function (JoinClause $join) {

                                        $join->on(
                                            's2.fixture_id',
                                            '=',
                                            's1.fixture_id'
                                        )

                                            ->where(
                                                's2.set_number',
                                                2
                                            );
                                    }
                                )

                                ->whereColumn(
                                    's1.fixture_id',
                                    'tf.id'
                                )

                                ->where('s1.set_number', 1)

                                ->where(function (Builder $result) {

                                    /*
                                 * KONDISI 1
                                 *
                                 * Satu tim menang dalam
                                 * dua set pertama.
                                 *
                                 * Pertandingan selesai 2-0.
                                 * Tidak perlu Set 3.
                                 */

                                    $result->where(
                                        function (Builder $sameWinner) {

                                            // Tim A menang kedua set.
                                            $sameWinner->where(
                                                function (Builder $a) {

                                                    $a->whereColumn(
                                                        's1.a_score',
                                                        '>',
                                                        's1.b_score'
                                                    )

                                                        ->whereColumn(
                                                            's2.a_score',
                                                            '>',
                                                            's2.b_score'
                                                        );
                                                }
                                            )

                                                // Atau Tim B menang kedua set.
                                                ->orWhere(
                                                    function (Builder $b) {

                                                        $b->whereColumn(
                                                            's1.a_score',
                                                            '<',
                                                            's1.b_score'
                                                        )

                                                            ->whereColumn(
                                                                's2.a_score',
                                                                '<',
                                                                's2.b_score'
                                                            );
                                                    }
                                                );

                                            /*
                                         * Jika sudah 2-0,
                                         * Set 3 tidak dimainkan.
                                         */

                                            $sameWinner->whereNotExists(
                                                fn($set3) => $set3
                                                    ->selectRaw('1')
                                                    ->from(
                                                        'tournament_fixture_sets as s3'
                                                    )
                                                    ->whereColumn(
                                                        's3.fixture_id',
                                                        'tf.id'
                                                    )
                                                    ->where(
                                                        's3.set_number',
                                                        3
                                                    )
                                            );
                                        }
                                    )


                                        /*
                                 * KONDISI 2
                                 *
                                 * Set pertama dan kedua
                                 * dimenangkan tim berbeda.
                                 *
                                 * Wajib ada Set 3.
                                 */

                                        ->orWhere(
                                            function (Builder $splitWinner) {

                                                // A menang Set 1, B menang Set 2.
                                                $splitWinner->where(
                                                    function (Builder $aThenB) {

                                                        $aThenB->whereColumn(
                                                            's1.a_score',
                                                            '>',
                                                            's1.b_score'
                                                        )

                                                            ->whereColumn(
                                                                's2.a_score',
                                                                '<',
                                                                's2.b_score'
                                                            );
                                                    }
                                                )

                                                    // Atau B menang Set 1, A menang Set 2.
                                                    ->orWhere(
                                                        function (Builder $bThenA) {

                                                            $bThenA->whereColumn(
                                                                's1.a_score',
                                                                '<',
                                                                's1.b_score'
                                                            )

                                                                ->whereColumn(
                                                                    's2.a_score',
                                                                    '>',
                                                                    's2.b_score'
                                                                );
                                                        }
                                                    );

                                                /*
                                         * Kedudukan 1-1:
                                         * Set 3 wajib tersimpan.
                                         */

                                                $splitWinner->whereExists(
                                                    fn($set3) => $set3
                                                        ->selectRaw('1')
                                                        ->from(
                                                            'tournament_fixture_sets as s3'
                                                        )
                                                        ->whereColumn(
                                                            's3.fixture_id',
                                                            'tf.id'
                                                        )
                                                        ->where(
                                                            's3.set_number',
                                                            3
                                                        )
                                                );
                                            }
                                        );
                                });
                        }
                    );
            });
    }
}
