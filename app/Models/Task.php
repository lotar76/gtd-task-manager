<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'context_id',
        'parent_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'due_date',
        'estimated_time',
        'end_time',
        'completed_at',
        'position',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    // Scope для фильтрации по GTD статусам
    public function scopeInbox($query)
    {
        return $query->where('status', 'inbox');
    }

    public function scopeNextAction($query)
    {
        return $query->where('status', 'next_action');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeSomeday($query)
    {
        return $query->where('status', 'someday');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope для фильтрации задач на сегодня (по статусу)
    public function scopeToday($query)
    {
        return $query->where('status', 'today');
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['completed']);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('due_date', '>', now()->toDateString())
            ->whereDate('due_date', '<=', now()->addWeek()->toDateString());
    }

    // Проверка просрочена ли задача
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status !== 'completed';
    }

    // Проверка на сегодня
    public function getIsTodayAttribute(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }
}

