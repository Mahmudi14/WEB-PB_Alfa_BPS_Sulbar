<?php

namespace Tests\Unit;

use App\Models\Tournament;
use App\Services\TournamentScoringService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TournamentScoringServiceTest extends TestCase
{
    private TournamentScoringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TournamentScoringService();
    }

    private function tournament(string $setFormat, int $target = 21): Tournament
    {
        return new Tournament(['set_format' => $setFormat, 'target_points' => $target]);
    }

    public function test_two_set_draw_has_draw_result(): void
    {
        $result = $this->service->evaluate($this->tournament('two_draw'), [
            ['a' => 21, 'b' => 19], ['a' => 17, 'b' => 21]
        ], null, null);
        $this->assertSame('draw', $result['result']);
        $this->assertNull($result['winner_side']);
    }

    public function test_aggregate_tie_requires_jus_and_decides_winner(): void
    {
        $result = $this->service->evaluate($this->tournament('two_aggregate'), [
            ['a' => 21, 'b' => 19], ['a' => 19, 'b' => 21]
        ], 3, 1);
        $this->assertSame('a', $result['winner_side']);
        $this->assertSame(3, $result['jus_a']);
    }

    public function test_three_set_match_finishes_after_two_wins(): void
    {
        $result = $this->service->evaluate($this->tournament('best_of_three', 15), [
            ['a' => 15, 'b' => 11], ['a' => 13, 'b' => 15], ['a' => 15, 'b' => 8]
        ], null, null);
        $this->assertSame('a', $result['winner_side']);
    }

    public function test_aggregate_total_tie_without_jus_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->evaluate($this->tournament('two_aggregate'), [
            ['a' => 21, 'b' => 19], ['a' => 19, 'b' => 21]
        ], null, null);
    }
}
