<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
    ];

    // Владелец workspace
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Участники workspace
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Цели
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    // Проекты
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // Задачи
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Контексты
    public function contexts(): HasMany
    {
        return $this->hasMany(Context::class);
    }

    // Теги
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    // Проверка, является ли пользователь участником
    public function hasMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists() 
            || $this->owner_id === $userId;
    }

    // Получить роль пользователя в workspace
    public function getMemberRole(int $userId): ?string
    {
        if ($this->owner_id === $userId) {
            return 'owner';
        }

        $member = $this->members()->where('user_id', $userId)->first();
        return $member?->pivot->role;
    }
}


