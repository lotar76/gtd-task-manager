<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Просмотр задачи (любой участник workspace)
    public function view(User $user, Task $task): bool
    {
        return $task->workspace->hasMember($user->id);
    }

    // Обновление задачи (создатель, назначенный, admin, owner)
    public function update(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id || $task->assigned_to === $user->id) {
            return true;
        }

        $role = $task->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    // Удаление задачи (создатель, admin, owner)
    public function delete(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id) {
            return true;
        }

        $role = $task->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    // Назначение задачи (admin, owner, создатель)
    public function assign(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id) {
            return true;
        }

        $role = $task->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    // Комментирование (любой участник workspace)
    public function comment(User $user, Task $task): bool
    {
        return $task->workspace->hasMember($user->id);
    }
}


