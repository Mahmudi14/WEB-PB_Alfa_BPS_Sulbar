<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = [
        'name',
        'description',
        'format',
        'match_type',
        'set_format',
        'target_points',
        'league_win_points',
        'league_draw_points',
        'league_loss_points',
        'league_legs',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_points' => 'integer',
            'league_win_points' => 'integer',
            'league_draw_points' => 'integer',
            'league_loss_points' => 'integer',
            'league_legs' => 'integer',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TournamentEntry::class)
            ->orderBy('seed')
            ->orderBy('id');
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(TournamentFixture::class)
            ->orderBy('round_number')
            ->orderBy('match_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function getSetFormatLabelAttribute(): string
    {
        return match ($this->set_format) {
            'two_draw' => '2 Set — Boleh Seri',
            'two_aggregate' => '2 Set — Total Poin',
            'best_of_three' => '3 Set — Best of 3',
            default => 'Sistem Set',
        };
    }
}
