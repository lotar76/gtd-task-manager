<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    // Все проекты пользователя
    public function all(Request $request): JsonResponse
    {
        $workspaceIds = [$request->user()->defaultWorkspace()->id];

        $query = Project::whereIn('workspace_id', $workspaceIds)
            ->with(['goal:id,name', 'creator'])
            ->withCount('tasks as total_tasks_count')
            ->withCount(['tasks as completed_tasks_count' => function ($query) {
                $query->whereNotNull('completed_at');
            }])
            ->withCount(['tasks as tasks_count' => function ($query) {
                $query->whereNull('completed_at');
            }]);

        if ($request->has('include_archived') && $request->include_archived) {
            // Показываем все проекты
        } else {
            $query->where(function($q) {
                $q->where('status', 'active')
                  ->orWhereNull('status');
            });
        }

        $projects = $query->orderBy('name')->get();

        return ApiResponse::success($projects, 'Список потоков получен');
    }

    // Создание проекта
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal_id' => 'nullable|exists:goals,id',
            'color' => 'nullable|string|size:7',
            'status' => 'nullable|in:active,archived,completed',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $project = Project::create($validated);
        $project->load(['goal:id,name', 'creator']);
        $project->loadCount('tasks');

        return ApiResponse::success($project, 'Поток создан', 201);
    }

    // Получение проекта
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project->load(['goal', 'creator', 'tasks']);
        return ApiResponse::success($project, 'Поток получен');
    }

    // Обновление проекта
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'goal_id' => 'nullable|exists:goals,id',
            'color' => 'nullable|string|size:7',
            'status' => 'sometimes|in:active,archived,completed',
        ]);

        $project->update($validated);

        $fresh = $project->fresh(['goal:id,name', 'creator'])->loadCount('tasks');

        return ApiResponse::success($fresh, 'Поток обновлен');
    }

    // Удаление проекта
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return ApiResponse::success(null, 'Поток удален');
    }

    // Задачи проекта
    public function tasks(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['project', 'context', 'assignee', 'tags', 'contacts'])
            ->orderBy('position')
            ->get();

        return ApiResponse::success($tasks, 'Задачи потока получены');
    }
}
