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
        'life_sphere_id',
        'title',
        'type',
        'timer_minutes',
        'subtasks',
        'progress_start',
        'progress_step',
        'progress_end',
        'progress_sets',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
        'timer_minutes' => 'integer',
        'subtasks' => 'array',
        'progress_start' => 'integer',
        'progress_step' => 'integer',
        'progress_end' => 'integer',
        'progress_sets' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lifeSphere(): BelongsTo
    {
        return $this->belongsTo(LifeSphere::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ChallengeEntry::class);
    }
}
