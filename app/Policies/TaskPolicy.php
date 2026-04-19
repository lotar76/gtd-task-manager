<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Просмотр задачи (создатель или связан через task_contact)
    public function view(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->contacts()
            ->where('contacts.contact_user_id', $user->id)
            ->exists();
    }

    // Обновление задачи (создатель или assignee)
    public function update(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id || $task->assigned_to === $user->id) {
            return true;
        }

        return $task->assignees()
            ->where('contacts.contact_user_id', $user->id)
            ->exists();
    }

    // Удаление задачи (только создатель)
    public function delete(User $user, Task $task): bool
    {
        return $task->created_by === $user->id;
    }

    // Назначение задачи (создатель)
    public function assign(User $user, Task $task): bool
    {
        return $task->created_by === $user->id;
    }

    // Комментирование (создатель или связан через task_contact)
    public function comment(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->contacts()
            ->where('contacts.contact_user_id', $user->id)
            ->exists();
    }
}
