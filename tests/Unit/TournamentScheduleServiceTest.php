<?php

namespace Tests\Unit;

use App\Services\TournamentScheduleService;
use PHPUnit\Framework\TestCase;

class TournamentScheduleServiceTest extends TestCase
{
    public function test_liga_covers_every_pair_once_even_with_odd_count(): void
    {
        foreach (range(2, 12) as $n) {
            $ids = range(1, $n);
            $rounds = TournamentScheduleService::leaguePlan($ids);
            $this->assertCount($n % 2 === 0 ? $n - 1 : $n, $rounds);
            $pairs = [];
            foreach ($rounds as $round) {
                $roundUsed = [];
                foreach ($round as $match) {
                    $a = $match['a']; $b = $match['b'];
                    $this->assertNotSame($a, $b);
                    $this->assertNotContains($a, $roundUsed);
                    $this->assertNotContains($b, $roundUsed);
                    $roundUsed[] = $a; $roundUsed[] = $b;
                    $pair = [$a, $b]; sort($pair);
                    $key = implode('-', $pair);
                    $this->assertArrayNotHasKey($key, $pairs);
                    $pairs[$key] = true;
                }
            }
            $this->assertCount($n * ($n - 1) / 2, $pairs);
        }
    }

    public function test_cup_bracket_has_all_entries_once_and_distributes_byes(): void
    {
        foreach (range(2, 32) as $n) {
            $rounds = TournamentScheduleService::cupPlan(range(1, $n));
            $size = 2;
            while ($size < $n) $size *= 2;
            $this->assertCount($size / 2, $rounds[0]);
            $seen = []; $byes = 0;
            foreach ($rounds[0] as $match) {
                if ($match['a'] !== null) $seen[] = $match['a'];
                if ($match['b'] !== null) $seen[] = $match['b'];
                $byes += (int) $match['bye'];
            }
            sort($seen);
            $this->assertSame(range(1, $n), $seen);
            $this->assertSame($size - $n, $byes);
            $this->assertCount(1, $rounds[count($rounds) - 1]);
        }
    }
}
