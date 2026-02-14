<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\LifeSphere;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LifeSphereController extends Controller
{
    // Все сферы пользователя (по всем workspace)
    public function all(Request $request): JsonResponse
    {
        $workspaceIds = $request->user()
            ->allWorkspaces()
            ->pluck('id');

        $spheres = LifeSphere::whereIn('workspace_id', $workspaceIds)
            ->orderBy('position')
            ->get();

        return ApiResponse::success($spheres, 'Список сфер получен');
    }

    // Список сфер workspace
    public function index(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $spheres = $workspace->lifeSpheres()
            ->orderBy('position')
            ->get();

        return ApiResponse::success($spheres, 'Список сфер получен');
    }

    // Создание сферы
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('createContent', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|size:7',
            'position' => 'nullable|integer|min:0',
        ]);

        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $sphere = LifeSphere::create($validated);

        return ApiResponse::success($sphere, 'Сфера создана', 201);
    }

    // Обновление сферы
    public function update(Request $request, Workspace $workspace, LifeSphere $lifeSphere): JsonResponse
    {
        $this->authorize('update', $lifeSphere);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|size:7',
            'position' => 'nullable|integer|min:0',
        ]);

        $lifeSphere->update($validated);

        return ApiResponse::success($lifeSphere->fresh(), 'Сфера обновлена');
    }

    // Удаление сферы
    public function destroy(Workspace $workspace, LifeSphere $lifeSphere): JsonResponse
    {
        $this->authorize('delete', $lifeSphere);

        $lifeSphere->delete();

        return ApiResponse::success(null, 'Сфера удалена');
    }

    // Заполнить дефолтными сферами
    public function seed(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('createContent', $workspace);

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
