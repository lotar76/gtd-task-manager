<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Goal;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    // Список целей
    public function index(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $goals = $workspace->goals()
            ->with(['creator'])
            ->withCount('projects')
            ->get();

        return ApiResponse::success($goals, 'Список целей получен');
    }

    // Создание цели
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('createContent', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7',
            'status' => 'nullable|in:active,archived,completed',
            'deadline' => 'nullable|date',
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $goal = Goal::create($validated);

        return ApiResponse::success($goal, 'Цель создана', 201);
    }

    // Получение цели
    public function show(Workspace $workspace, Goal $goal): JsonResponse
    {
        $this->authorize('view', $goal);

        $goal->load(['creator', 'projects.tasks']);

        return ApiResponse::success($goal, 'Цель получена');
    }

    // Обновление цели
    public function update(Request $request, Workspace $workspace, Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7',
            'status' => 'sometimes|in:active,archived,completed',
            'deadline' => 'nullable|date',
        ]);

        $goal->update($validated);

        return ApiResponse::success($goal->fresh(), 'Цель обновлена');
    }

    // Удаление цели
    public function destroy(Workspace $workspace, Goal $goal): JsonResponse
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return ApiResponse::success(null, 'Цель удалена');
    }
}


