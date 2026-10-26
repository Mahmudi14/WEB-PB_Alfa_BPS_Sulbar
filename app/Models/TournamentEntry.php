<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TournamentEntry extends Model
{
    protected $fillable = ['tournament_id', 'seed'];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'tournament_entry_players', 'entry_id', 'player_id')
            ->withTrashed()->withPivot('slot')->orderByPivot('slot');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->players->pluck('name')->implode(' / ') ?: 'Peserta belum ditentukan';
    }
}
