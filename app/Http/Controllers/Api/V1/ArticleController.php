<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $query = Article::where('workspace_id', $workspace->id)
            ->with(['creator:id,name', 'folder:id,name', 'author:id,name'])
            ->orderBy('updated_at', 'desc');

        if ($request->has('article_folder_id')) {
            $query->where('article_folder_id', $request->article_folder_id);
        }

        $articles = $query->get();

        return ApiResponse::success($articles, 'Список статей');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'article_author_id' => 'nullable|exists:article_authors,id',
            'article_folder_id' => 'required|exists:article_folders,id',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $article = Article::create($validated);
        $article->load(['creator:id,name', 'folder:id,name', 'author:id,name']);

        return ApiResponse::success($article, 'Статья добавлена', 201);
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'article_author_id' => 'nullable|exists:article_authors,id',
            'article_folder_id' => 'sometimes|exists:article_folders,id',
        ]);

        $article->update($validated);

        $fresh = $article->fresh();
        $fresh->load(['creator:id,name', 'folder:id,name', 'author:id,name']);

        return ApiResponse::success($fresh, 'Статья обновлена');
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return ApiResponse::success(null, 'Статья удалена');
    }
}
