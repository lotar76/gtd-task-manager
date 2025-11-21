<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    // Список проектов
    public function index(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $query = $workspace->projects()
            ->with(['goal', 'creator'])
            ->withCount('tasks');

        // Фильтр по статусу (по умолчанию только активные)
        if ($request->has('include_archived') && $request->include_archived) {
            // Показываем все проекты
        } else {
            $query->where(function($q) {
                $q->where('status', 'active')
                  ->orWhereNull('status');
            });
        }

        $projects = $query->orderBy('name')->get();

        return ApiResponse::success($projects, 'Список проектов получен');
    }

    // Создание проекта
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('createContent', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal_id' => 'nullable|exists:goals,id',
            'color' => 'nullable|string|size:7',
            'status' => 'nullable|in:active,archived,completed',
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $project = Project::create($validated);

        return ApiResponse::success($project, 'Проект создан', 201);
    }

    // Получение проекта
    public function show(Workspace $workspace, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['goal', 'creator', 'tasks']);

        return ApiResponse::success($project, 'Проект получен');
    }

    // Обновление проекта
    public function update(Request $request, Workspace $workspace, Project $project): JsonResponse
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

        return ApiResponse::success($project->fresh(), 'Проект обновлен');
    }

    // Удаление проекта
    public function destroy(Workspace $workspace, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return ApiResponse::success(null, 'Проект удален');
    }

    // Задачи проекта
    public function tasks(Workspace $workspace, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['context', 'assignee', 'tags'])
            ->orderBy('position')
            ->get();

        return ApiResponse::success($tasks, 'Задачи проекта получены');
    }
}

