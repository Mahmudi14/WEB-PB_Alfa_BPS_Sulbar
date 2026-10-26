<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentFixture extends Model
{
    protected $fillable = [
        'tournament_id', 'round_number', 'match_number', 'a_entry_id', 'b_entry_id',
        'winner_entry_id', 'next_fixture_id', 'next_slot', 'result', 'is_bye',
        'shuttlecock_count', 'jus_a', 'jus_b',
    ];

    protected function casts(): array
    {
        return [
            'is_bye' => 'boolean', 'round_number' => 'integer', 'match_number' => 'integer',
            'shuttlecock_count' => 'integer', 'jus_a' => 'integer', 'jus_b' => 'integer',
            'a_entry_id' => 'integer', 'b_entry_id' => 'integer',
            'winner_entry_id' => 'integer', 'next_fixture_id' => 'integer',
        ];
    }

    public function tournament(): BelongsTo { return $this->belongsTo(Tournament::class); }
    public function entryA(): BelongsTo { return $this->belongsTo(TournamentEntry::class, 'a_entry_id'); }
    public function entryB(): BelongsTo { return $this->belongsTo(TournamentEntry::class, 'b_entry_id'); }
    public function winner(): BelongsTo { return $this->belongsTo(TournamentEntry::class, 'winner_entry_id'); }
    public function nextFixture(): BelongsTo { return $this->belongsTo(self::class, 'next_fixture_id'); }
    public function sets(): HasMany { return $this->hasMany(TournamentFixtureSet::class, 'fixture_id')->orderBy('set_number'); }

    public function getIsCompletedAttribute(): bool
    {
        return $this->is_bye || $this->result !== null;
    }
}
