<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Workspace;
use App\Services\WorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    public function __construct(
        private readonly WorkspaceService $workspaceService
    ) {
    }

    // Список всех workspace пользователя
    public function index(): JsonResponse
    {
        $workspaces = $this->workspaceService->getUserWorkspaces(Auth::id());
        
        return ApiResponse::success($workspaces, 'Список workspace получен');
    }

    // Создание workspace
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:workspaces,slug',
            'description' => 'nullable|string',
        ]);

        $workspace = $this->workspaceService->createWorkspace($validated, Auth::id());

        return ApiResponse::success($workspace, 'Workspace создан', 201);
    }

    // Получение workspace
    public function show(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $workspace->load(['owner', 'members', 'goals', 'projects']);

        return ApiResponse::success($workspace, 'Workspace получен');
    }

    // Обновление workspace
    public function update(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workspace = $this->workspaceService->updateWorkspace($workspace, $validated);

        return ApiResponse::success($workspace, 'Workspace обновлен');
    }

    // Удаление workspace
    public function destroy(Workspace $workspace): JsonResponse
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return ApiResponse::success(null, 'Workspace удален');
    }

    // Список участников
    public function members(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $members = $workspace->members()->withPivot('role')->get();
        
        // Добавляем владельца в список, если его там нет
        $owner = $workspace->owner;
        if ($owner && !$members->contains('id', $owner->id)) {
            // Создаем pivot объект для владельца
            $owner->pivot = new \stdClass();
            $owner->pivot->role = 'owner';
            $members->prepend($owner);
        }

        return ApiResponse::success($members, 'Список участников получен');
    }

    // Добавление участника
    public function addMember(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('manageMembers', $workspace);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email',
            'role' => 'required|in:admin,member,viewer',
        ]);

        // Определяем user_id либо напрямую, либо по email
        if (isset($validated['email'])) {
            $user = \App\Models\User::where('email', $validated['email'])->first();
            if (!$user) {
                return ApiResponse::error('Пользователь с таким email не найден', 404);
            }
            $userId = $user->id;
        } elseif (isset($validated['user_id'])) {
            $userId = $validated['user_id'];
        } else {
            return ApiResponse::error('Необходимо указать user_id или email', 422);
        }

        // Проверяем, не является ли пользователь уже участником
        if ($workspace->hasMember($userId)) {
            return ApiResponse::error('Пользователь уже является участником workspace', 422);
        }

        $this->workspaceService->addMember($workspace, $userId, $validated['role']);

        return ApiResponse::success(null, 'Участник добавлен');
    }

    // Удаление участника
    public function removeMember(Workspace $workspace, int $userId): JsonResponse
    {
        $this->authorize('manageMembers', $workspace);

        $this->workspaceService->removeMember($workspace, $userId);

        return ApiResponse::success(null, 'Участник удален');
    }

    // Обновление роли участника
    public function updateMemberRole(Request $request, Workspace $workspace, int $userId): JsonResponse
    {
        $this->authorize('manageMembers', $workspace);

        $validated = $request->validate([
            'role' => 'required|in:owner,admin,member,viewer',
        ]);

        $this->workspaceService->updateMemberRole($workspace, $userId, $validated['role']);

        return ApiResponse::success(null, 'Роль участника обновлена');
    }
}

