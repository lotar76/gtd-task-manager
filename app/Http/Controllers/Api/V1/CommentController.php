<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskChanged;
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

        // Push-уведомление участникам
        $changer = Auth::user();
        $shortText = mb_strlen($validated['content']) > 60
            ? mb_substr($validated['content'], 0, 60) . '…'
            : $validated['content'];

        $recipientIds = collect();

        // Creator
        if ($task->created_by && $task->created_by !== $changer->id) {
            $recipientIds->push($task->created_by);
        }

        // Contacts linked to users
        $contactUserIds = $task->contacts()
            ->whereNotNull('contacts.contact_user_id')
            ->pluck('contacts.contact_user_id');
        $recipientIds = $recipientIds->merge($contactUserIds)
            ->unique()
            ->reject(fn ($id) => $id === $changer->id);

        foreach ($recipientIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->notify(new TaskChanged($task, $changer->name, ["комментарий: {$shortText}"]));
            }
        }

        return ApiResponse::success($comment, 'Комментарий добавлен', 201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return ApiResponse::success(null, 'Комментарий удален');
    }
}
