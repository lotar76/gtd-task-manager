<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Goal;
use App\Models\User;

class GoalPolicy
{
    // Просмотр цели
    public function view(User $user, Goal $goal): bool
    {
        return $goal->workspace->hasMember($user->id);
    }

    // Обновление цели
    public function update(User $user, Goal $goal): bool
    {
        if ($goal->created_by === $user->id) {
            return true;
        }

        $role = $goal->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    // Удаление цели
    public function delete(User $user, Goal $goal): bool
    {
        if ($goal->created_by === $user->id) {
            return true;
        }

        $role = $goal->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }
}


