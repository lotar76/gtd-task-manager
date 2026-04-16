<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Comment;
use App\Models\Task;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $comments = $task->comments()->with('user:id,name')->oldest()->get();

        return ApiResponse::success($comments, 'Комментарии получены');
    }

    public function store(Request $request, Task $task, TelegramService $telegram): JsonResponse
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        $comment->load('user:id,name');

        // Уведомляем участников задачи
        $telegram->notifyTaskParticipants($task, Auth::user(), 'commented', $validated['content']);

        return ApiResponse::success($comment, 'Комментарий добавлен', 201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return ApiResponse::success(null, 'Комментарий удален');
    }
}
