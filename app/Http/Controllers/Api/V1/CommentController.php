<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Comment;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Список комментариев задачи
    public function index(Workspace $workspace, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $comments = $task->comments()->with('user')->latest()->get();

        return ApiResponse::success($comments, 'Комментарии получены');
    }

    // Создание комментария
    public function store(Request $request, Workspace $workspace, Task $task): JsonResponse
    {
        $this->authorize('comment', $task);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        return ApiResponse::success($comment, 'Комментарий добавлен', 201);
    }

    // Удаление комментария
    public function destroy(Workspace $workspace, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return ApiResponse::success(null, 'Комментарий удален');
    }
}


