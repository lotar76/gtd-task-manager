<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeEntry extends Model
{
    protected $fillable = [
        'challenge_id',
        'date',
        'completed',
        'subtask_states',
        'timer_seconds',
    ];

    protected $casts = [
        'date' => 'date',
        'completed' => 'boolean',
        'subtask_states' => 'array',
        'timer_seconds' => 'integer',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }
}
