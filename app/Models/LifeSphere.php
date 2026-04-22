<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LifeSphere extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'color',
        'description',
        'position',
        'is_hidden',
        'created_by',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(LifeSphereImage::class)->orderBy('position');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(LifeSphereImage::class)->orderBy('position');
    }
}
