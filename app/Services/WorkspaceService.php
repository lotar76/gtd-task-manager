<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Str;

class WorkspaceService
{
    // Создание workspace
    public function createWorkspace(array $data, int $ownerId): Workspace
    {
        $data['owner_id'] = $ownerId;
        
        // Автоматическая генерация slug
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);
        }

        $workspace = Workspace::create($data);

        // Добавляем владельца как участника с ролью owner
        $workspace->members()->attach($ownerId, ['role' => 'owner']);

        return $workspace;
    }

    // Обновление workspace
    public function updateWorkspace(Workspace $workspace, array $data): Workspace
    {
        $workspace->update($data);
        return $workspace->fresh();
    }

    // Добавление участника
    public function addMember(Workspace $workspace, int $userId, string $role = 'member'): void
    {
        if (!$workspace->hasMember($userId)) {
            $workspace->members()->attach($userId, ['role' => $role]);
        }
    }

    // Удаление участника
    public function removeMember(Workspace $workspace, int $userId): void
    {
        // Нельзя удалить владельца
        if ($workspace->owner_id !== $userId) {
            $workspace->members()->detach($userId);
        }
    }

    // Изменение роли участника
    public function updateMemberRole(Workspace $workspace, int $userId, string $role): void
    {
        // Нельзя изменить роль владельца
        if ($workspace->owner_id !== $userId) {
            $workspace->members()->updateExistingPivot($userId, ['role' => $role]);
        }
    }

    // Передача владения
    public function transferOwnership(Workspace $workspace, int $newOwnerId): Workspace
    {
        $oldOwnerId = $workspace->owner_id;
        
        $workspace->update(['owner_id' => $newOwnerId]);
        
        // Обновляем роли
        $workspace->members()->updateExistingPivot($newOwnerId, ['role' => 'owner']);
        $workspace->members()->updateExistingPivot($oldOwnerId, ['role' => 'admin']);
        
        return $workspace->fresh();
    }

    // Получить все workspace пользователя
    public function getUserWorkspaces(int $userId)
    {
        // Получаем workspace через таблицу workspace_members
        return Workspace::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    // Создание персонального workspace при регистрации
    public function createPersonalWorkspace(int $userId, string $userName): Workspace
    {
        $workspaceName = $userName . ' - Личные задачи';
        
        return $this->createWorkspace([
            'name' => $workspaceName,
            'description' => 'Персональное рабочее пространство для личных задач',
        ], $userId);
    }
}

