<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ArticleAuthor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleAuthorController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $query = ArticleAuthor::where('workspace_id', $workspace->id)
            ->with(['creator:id,name'])
            ->orderBy('name');

        if ($request->has('article_folder_id')) {
            $query->where('article_folder_id', $request->article_folder_id);
        }

        $authors = $query->get();

        return ApiResponse::success($authors, 'Список авторов');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'article_folder_id' => 'required|exists:article_folders,id',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $author = ArticleAuthor::create($validated);
        $author->load(['creator:id,name']);

        return ApiResponse::success($author, 'Автор добавлен', 201);
    }

    public function update(Request $request, ArticleAuthor $articleAuthor): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $articleAuthor->update($validated);

        $fresh = $articleAuthor->fresh();
        $fresh->load(['creator:id,name']);

        return ApiResponse::success($fresh, 'Автор обновлен');
    }

    public function destroy(ArticleAuthor $articleAuthor): JsonResponse
    {
        $articleAuthor->delete();

        return ApiResponse::success(null, 'Автор удален');
    }
}
