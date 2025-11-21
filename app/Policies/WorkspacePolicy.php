<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    // Просмотр workspace
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->hasMember($user->id);
    }

    // Обновление workspace
    public function update(User $user, Workspace $workspace): bool
    {
        $role = $workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    // Удаление workspace (только владелец)
    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }

    // Управление участниками (owner, admin)
    public function manageMembers(User $user, Workspace $workspace): bool
    {
        $role = $workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    // Создание задач/проектов
    public function createContent(User $user, Workspace $workspace): bool
    {
        $role = $workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin', 'member']);
    }
}


