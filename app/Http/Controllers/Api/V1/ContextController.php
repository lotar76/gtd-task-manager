<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContextController extends Controller
{
    // Все контексты пользователя
    public function all(Request $request): JsonResponse
    {
        $workspaceIds = [$request->user()->defaultWorkspace()->id];

        $contexts = Context::whereIn('workspace_id', $workspaceIds)
            ->withCount('tasks')
            ->get();

        return ApiResponse::success($contexts, 'Список контекстов получен');
    }

    // Создание контекста
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|size:7',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;

        $context = Context::create($validated);

        return ApiResponse::success($context, 'Контекст создан', 201);
    }

    // Получение контекста
    public function show(Context $context): JsonResponse
    {
        $context->load(['tasks.project', 'tasks.context', 'tasks.assignee', 'tasks.tags']);
        return ApiResponse::success($context, 'Контекст получен');
    }

    // Обновление контекста
    public function update(Request $request, Context $context): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|size:7',
        ]);

        $context->update($validated);

        return ApiResponse::success($context->fresh(), 'Контекст обновлен');
    }

    // Удаление контекста
    public function destroy(Context $context): JsonResponse
    {
        $context->delete();
        return ApiResponse::success(null, 'Контекст удален');
    }
}
