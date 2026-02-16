<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'life_sphere_id',
        'name',
        'description',
        'color',
        'status',
        'deadline',
        'bible_verse',
        'image_path',
        'image_url',
        'created_by',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function lifeSphere(): BelongsTo
    {
        return $this->belongsTo(LifeSphere::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function directTasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Получить все задачи (через проекты + напрямую привязанные)
    public function tasks()
    {
        $projectTaskIds = Task::whereIn('project_id', $this->projects()->pluck('id'))->pluck('id');
        $directTaskIds = $this->directTasks()->pluck('id');

        return Task::whereIn('id', $projectTaskIds->merge($directTaskIds)->unique());
    }

    // Прогресс цели (% выполненных задач)
    public function getProgressAttribute(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->whereNotNull('completed_at')->count();
        return (int) (($completedTasks / $totalTasks) * 100);
    }
}


