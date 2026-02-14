<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LifeSphere;
use App\Models\User;

class LifeSpherePolicy
{
    public function view(User $user, LifeSphere $lifeSphere): bool
    {
        return $lifeSphere->workspace->hasMember($user->id);
    }

    public function update(User $user, LifeSphere $lifeSphere): bool
    {
        if ($lifeSphere->created_by === $user->id) {
            return true;
        }

        $role = $lifeSphere->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }

    public function delete(User $user, LifeSphere $lifeSphere): bool
    {
        if ($lifeSphere->created_by === $user->id) {
            return true;
        }

        $role = $lifeSphere->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }
}
