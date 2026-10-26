<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentFixtureSet extends Model
{
    public $timestamps = false;
    protected $fillable = ['fixture_id', 'set_number', 'a_score', 'b_score'];

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(TournamentFixture::class, 'fixture_id');
    }
}
