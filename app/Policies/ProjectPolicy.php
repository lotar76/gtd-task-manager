<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    // Просмотр проекта
    public function view(User $user, Project $project): bool
    {
        return $project->workspace->hasMember($user->id);
    }

    // Обновление проекта
    public function update(User $user, Project $project): bool
    {
        if ($project->created_by === $user->id) {
            return true;
        }

        $role = $project->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    // Удаление проекта
    public function delete(User $user, Project $project): bool
    {
        if ($project->created_by === $user->id) {
            return true;
        }

        $role = $project->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }
}


