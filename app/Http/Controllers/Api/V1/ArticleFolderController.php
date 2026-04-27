<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ArticleFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleFolderController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $folders = ArticleFolder::where('workspace_id', $workspace->id)
            ->with(['creator:id,name'])
            ->withCount('articles')
            ->orderBy('name')
            ->get();

        return ApiResponse::success($folders, 'Список папок');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $folder = ArticleFolder::create($validated);
        $folder->load(['creator:id,name']);
        $folder->loadCount('articles');

        return ApiResponse::success($folder, 'Папка создана', 201);
    }

    public function update(Request $request, ArticleFolder $articleFolder): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $articleFolder->update($validated);

        $fresh = $articleFolder->fresh();
        $fresh->load(['creator:id,name']);
        $fresh->loadCount('articles');

        return ApiResponse::success($fresh, 'Папка обновлена');
    }

    public function destroy(ArticleFolder $articleFolder): JsonResponse
    {
        $articleFolder->delete();

        return ApiResponse::success(null, 'Папка удалена');
    }
}
