<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchParticipant extends Model
{
    protected $fillable = [
        'match_id',
        'player_id',
        'team',
        'slot',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(
            BadmintonMatch::class,
            'match_id'
        );
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class)
            ->withTrashed();
    }
}
