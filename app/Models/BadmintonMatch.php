<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BadmintonMatch extends Model
{
    use SoftDeletes;

    protected $table = 'matches';

    protected $fillable = [
        'played_on',
        'type',
        'result',
        'shuttlecock_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'played_on' => 'date',
            'shuttlecock_count' => 'integer',
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(
            MatchParticipant::class,
            'match_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function editableBy(?User $user): bool
    {
        if (!$user || !$user->is_active) {
            return false;
        }

        if ($user->canManage()) {
            return true;
        }

        return $user->role === 'pemain'
            && $user->player !== null
            && $this->created_by === $user->id
            && now()->lt(
                $this->created_at->copy()->addHours(24)
            );
    }
}
