<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    // Все теги пользователя
    public function all(Request $request): JsonResponse
    {
        $workspaceIds = $request->user()
            ->allWorkspaces()
            ->pluck('id');

        $tags = Tag::whereIn('workspace_id', $workspaceIds)
            ->withCount('tasks')
            ->get();

        return ApiResponse::success($tags, 'Список тегов получен');
    }

    // Создание тега
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|size:7',
        ]);

        $workspace = $request->user()->allWorkspaces()->first();
        $validated['workspace_id'] = $workspace->id;

        $tag = Tag::create($validated);

        return ApiResponse::success($tag, 'Тег создан', 201);
    }

    // Получение тега
    public function show(Tag $tag): JsonResponse
    {
        $tag->load(['tasks.project', 'tasks.context', 'tasks.assignee', 'tasks.tags']);
        return ApiResponse::success($tag, 'Тег получен');
    }

    // Обновление тега
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|size:7',
        ]);

        $tag->update($validated);

        return ApiResponse::success($tag->fresh(), 'Тег обновлен');
    }

    // Удаление тега
    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();
        return ApiResponse::success(null, 'Тег удален');
    }
}
