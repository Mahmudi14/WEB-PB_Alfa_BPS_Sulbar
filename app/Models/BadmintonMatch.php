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
        'set1_a',
        'set1_b',
        'set2_a',
        'set2_b',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'played_on' => 'date',
            'shuttlecock_count' => 'integer',
            'set1_a' => 'integer',
            'set1_b' => 'integer',
            'set2_a' => 'integer',
            'set2_b' => 'integer',
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

        // Admin tidak memiliki batas waktu.
        if ($user->isAdmin()) {
            return true;
        }

        // Pengguna lainnya maksimal 24 jam.
        if (
            !$this->created_at ||
            now()->greaterThanOrEqualTo(
                $this->created_at->copy()->addHours(24)
            )
        ) {
            return false;
        }

        // Pengelola dapat mengedit semua pertandingan
        // selama masih dalam batas 24 jam.
        if ($user->role === 'pengelola') {
            return true;
        }

        // Pemain hanya pertandingan yang dibuat sendiri.
        return $user->role === 'pemain'
            && $user->player !== null
            && (int) $this->created_by === (int) $user->id;
    }
}