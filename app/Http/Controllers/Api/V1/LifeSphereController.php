<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\LifeSphere;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LifeSphereController extends Controller
{
    // Все сферы пользователя
    public function all(Request $request): JsonResponse
    {
        $workspaceIds = $request->user()
            ->allWorkspaces()
            ->pluck('id');

        $spheres = LifeSphere::whereIn('workspace_id', $workspaceIds)
            ->withCount('tasks')
            ->orderBy('position')
            ->get();

        return ApiResponse::success($spheres, 'Список сфер получен');
    }

    // Создание сферы
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|size:7',
            'position' => 'nullable|integer|min:0',
        ]);

        $workspace = $request->user()->allWorkspaces()->first();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $sphere = LifeSphere::create($validated);

        return ApiResponse::success($sphere, 'Сфера создана', 201);
    }

    // Получение сферы
    public function show(LifeSphere $lifeSphere): JsonResponse
    {
        $lifeSphere->load(['tasks.project', 'tasks.context', 'tasks.assignee', 'tasks.tags']);
        return ApiResponse::success($lifeSphere, 'Сфера получена');
    }

    // Обновление сферы
    public function update(Request $request, LifeSphere $lifeSphere): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|size:7',
            'position' => 'nullable|integer|min:0',
            'is_hidden' => 'sometimes|boolean',
        ]);

        $lifeSphere->update($validated);

        return ApiResponse::success($lifeSphere->fresh()->loadCount('tasks'), 'Сфера обновлена');
    }

    // Удаление сферы
    public function destroy(LifeSphere $lifeSphere): JsonResponse
    {
        $tasksCount = $lifeSphere->tasks()->count();
        if ($tasksCount > 0) {
            return ApiResponse::error(
                "Нельзя удалить сферу с привязанными задачами ({$tasksCount}). Сначала открепите задачи или скройте сферу.",
                422
            );
        }

        $lifeSphere->delete();

        return ApiResponse::success(null, 'Сфера удалена');
    }

    // Заполнить дефолтными сферами
    public function seed(Request $request): JsonResponse
    {
        $workspace = $request->user()->allWorkspaces()->first();

        $defaults = [
            ['name' => 'Духовная', 'color' => '#8B5CF6', 'position' => 0],
            ['name' => 'Семья', 'color' => '#EC4899', 'position' => 1],
            ['name' => 'Здоровье', 'color' => '#10B981', 'position' => 2],
            ['name' => 'Финансы', 'color' => '#F59E0B', 'position' => 3],
            ['name' => 'Работа', 'color' => '#3B82F6', 'position' => 4],
            ['name' => 'Развитие', 'color' => '#6366F1', 'position' => 5],
            ['name' => 'Окружение', 'color' => '#14B8A6', 'position' => 6],
            ['name' => 'Отдых', 'color' => '#F97316', 'position' => 7],
        ];

        $created = [];
        foreach ($defaults as $sphere) {
            $created[] = LifeSphere::create([
                ...$sphere,
                'workspace_id' => $workspace->id,
                'created_by' => Auth::id(),
            ]);
        }

        return ApiResponse::success($created, 'Сферы по умолчанию созданы', 201);
    }
}
