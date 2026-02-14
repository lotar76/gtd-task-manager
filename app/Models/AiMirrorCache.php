<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMirrorCache extends Model
{
    protected $table = 'ai_mirror_cache';

    protected $fillable = [
        'workspace_id',
        'period',
        'period_key',
        'response_json',
        'generated_at',
    ];

    protected $casts = [
        'response_json' => 'array',
        'generated_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
