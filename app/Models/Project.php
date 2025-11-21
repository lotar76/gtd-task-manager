<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'goal_id',
        'name',
        'description',
        'color',
        'status',
        'created_by',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Прогресс проекта (% выполненных задач)
    public function getProgressAttribute(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        return (int) (($completedTasks / $totalTasks) * 100);
    }

    // Количество активных задач
    public function getActiveTasksCountAttribute(): int
    {
        return $this->tasks()
            ->whereIn('status', ['inbox', 'next_action', 'waiting', 'someday'])
            ->count();
    }
}


