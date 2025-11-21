<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    // Просмотр комментария
    public function view(User $user, Comment $comment): bool
    {
        return $comment->task->workspace->hasMember($user->id);
    }

    // Удаление комментария (автор или admin/owner)
    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return true;
        }

        $role = $comment->task->workspace->getMemberRole($user->id);
        return in_array($role, ['owner', 'admin']);
    }
}


