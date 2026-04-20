<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'type',
        'timer_minutes',
        'subtasks',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
        'timer_minutes' => 'integer',
        'subtasks' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ChallengeEntry::class);
    }
}
