<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CompletedMatchQuery
{
    public static function regular(): Builder
    {
        return DB::table('matches as m')
            ->whereNull('m.deleted_at')
            ->whereIn('m.result', ['a', 'b', 'seri'])
            ->whereNotNull('m.played_on')
            ->whereNotNull('m.set1_a')
            ->whereNotNull('m.set1_b')
            ->whereNotNull('m.set2_a')
            ->whereNotNull('m.set2_b')
            ->whereExists(fn($q) => $q->selectRaw('1')
                ->from('match_participants as pa')
                ->whereColumn('pa.match_id', 'm.id')
                ->where('pa.team', 'a'))
            ->whereExists(fn($q) => $q->selectRaw('1')
                ->from('match_participants as pb')
                ->whereColumn('pb.match_id', 'm.id')
                ->where('pb.team', 'b'));
    }

    public static function tournament(): Builder
    {
        return DB::table('tournament_fixtures as tf')
            ->join('tournaments as t', 't.id', '=', 'tf.tournament_id')
            ->whereIn('t.format', ['liga', 'cup'])
            ->whereIn('t.status', ['active', 'completed'])
            ->where('tf.is_bye', false)
            ->whereNotNull('tf.played_on')
            ->whereNotNull('tf.a_entry_id')
            ->whereNotNull('tf.b_entry_id')
            ->whereIn('tf.result', ['a', 'b', 'draw'])
            ->whereExists(fn($q) => $q->selectRaw('1')
                ->from('tournament_fixture_sets as fs')
                ->whereColumn('fs.fixture_id', 'tf.id'));
    }
}
