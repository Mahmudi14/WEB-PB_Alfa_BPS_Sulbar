<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'gender',
        'initial_score',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'initial_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
