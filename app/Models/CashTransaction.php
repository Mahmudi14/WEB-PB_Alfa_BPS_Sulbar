<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_date',
        'type',
        'amount',
        'description',
        'shuttlecock_player_id',
        'shuttlecock_count',
        'shuttlecock_review_required',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'integer',
            'shuttlecock_player_id' => 'integer',
            'shuttlecock_count' => 'integer',
            'shuttlecock_review_required' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}
