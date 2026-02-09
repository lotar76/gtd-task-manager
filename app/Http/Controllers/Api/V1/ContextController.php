<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Context;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    // Список контекстов
    public function index(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $contexts = $workspace->contexts()->withCount('tasks')->get();

        return ApiResponse::success($contexts, 'Список контекстов получен');
    }

    // Создание контекста
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('createContent', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|size:7',
        ]);

        $validated['workspace_id'] = $workspace->id;

        $context = Context::create($validated);

        return ApiResponse::success($context, 'Контекст создан', 201);
    }

    // Получение контекста
    public function show(Workspace $workspace, Context $context): JsonResponse
    {
        $this->authorize('view', $workspace);

        $context->load(['tasks.workspace', 'tasks.project', 'tasks.context', 'tasks.assignee', 'tasks.tags']);

        return ApiResponse::success($context, 'Контекст получен');
    }

    // Обновление контекста
    public function update(Request $request, Workspace $workspace, Context $context): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|size:7',
        ]);

        $context->update($validated);

        return ApiResponse::success($context->fresh(), 'Контекст обновлен');
    }

    // Удаление контекста
    public function destroy(Workspace $workspace, Context $context): JsonResponse
    {
        $this->authorize('update', $workspace);

        $context->delete();

        return ApiResponse::success(null, 'Контекст удален');
    }
}


