<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Tag;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // Список тегов
    public function index(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $tags = $workspace->tags()->withCount('tasks')->get();

        return ApiResponse::success($tags, 'Список тегов получен');
    }

    // Создание тега
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('createContent', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|size:7',
        ]);

        $validated['workspace_id'] = $workspace->id;

        $tag = Tag::create($validated);

        return ApiResponse::success($tag, 'Тег создан', 201);
    }

    // Получение тега
    public function show(Workspace $workspace, Tag $tag): JsonResponse
    {
        $this->authorize('view', $workspace);

        $tag->load('tasks');

        return ApiResponse::success($tag, 'Тег получен');
    }

    // Обновление тега
    public function update(Request $request, Workspace $workspace, Tag $tag): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|size:7',
        ]);

        $tag->update($validated);

        return ApiResponse::success($tag->fresh(), 'Тег обновлен');
    }

    // Удаление тега
    public function destroy(Workspace $workspace, Tag $tag): JsonResponse
    {
        $this->authorize('update', $workspace);

        $tag->delete();

        return ApiResponse::success(null, 'Тег удален');
    }
}


