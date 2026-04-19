<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly \App\Services\TelegramService $telegram,
    ) {
    }

    // Все задачи пользователя (включая завершённые)
    public function all(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->where(fn ($q) => $this->scopeVisibleToUser($q, $request->user()))
            ->with(['project:id,name', 'context:id,name', 'assignee:id,name', 'creator:id,name', 'tags:id,name', 'lifeSphere:id,name,color', 'contacts', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success(['tasks' => $tasks]);
    }

    /**
     * Scope: задачи видны пользователю если они в его дефолтном пространстве
     * ИЛИ он указан через task_contact (contact.contact_user_id = user.id).
     */
    private function scopeVisibleToUser($query, \App\Models\User $user): void
    {
        $workspaceId = $user->defaultWorkspace()->id;
        $userId = $user->id;
        $query->where(function ($q) use ($workspaceId, $userId) {
            $q->where('workspace_id', $workspaceId)
              ->orWhereHas('contacts', fn ($c) => $c->where('contacts.contact_user_id', $userId));
        });
    }

    // GTD: Inbox
    public function inbox(Request $request): JsonResponse
    {
        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getInbox($request->user(), $userId);
        return ApiResponse::success($tasks, 'Входящие задачи получены');
    }

    // GTD: Next Actions
    public function nextActions(Request $request): JsonResponse
    {
        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getNextActions($request->user(), $userId);
        return ApiResponse::success($tasks, 'Следующие действия получены');
    }

    // GTD: Waiting
    public function waiting(Request $request): JsonResponse
    {
        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getWaiting($request->user(), $userId);
        return ApiResponse::success($tasks, 'Задачи в ожидании получены');
    }

    // GTD: Someday
    public function someday(Request $request): JsonResponse
    {
        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getSomeday($request->user(), $userId);
        return ApiResponse::success($tasks, 'Задачи "Когда-нибудь" получены');
    }

    // Задачи на сегодня
    public function today(Request $request): JsonResponse
    {
        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getToday($request->user(), $userId);
        return ApiResponse::success($tasks, 'Задачи на сегодня получены');
    }

    // Задачи на завтра
    public function tomorrow(Request $request): JsonResponse
    {
        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getTomorrow($request->user(), $userId);
        return ApiResponse::success($tasks, 'Задачи на завтра получены');
    }

    // Мои задачи
    public function myTasks(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->where(fn ($q) => $this->scopeVisibleToUser($q, $request->user()))
            ->where('assigned_to', Auth::id())
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts'])
            ->orderBy('due_date', 'asc')
            ->get();

        return ApiResponse::success($tasks, 'Мои задачи получены');
    }

    // Календарь задач
    public function calendar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'my_tasks' => 'boolean',
        ]);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getCalendarTasks(
            $request->user(),
            $validated['start_date'],
            $validated['end_date'],
            $userId
        );

        return ApiResponse::success($tasks, 'Задачи для календаря получены');
    }

    // Счётчики задач
    public function counts(Request $request): JsonResponse
    {
        $userId = $request->my_tasks ? Auth::id() : null;
        $counts = $this->taskService->getTaskCounts($request->user(), $userId);
        return ApiResponse::success($counts, 'Счетчики задач получены');
    }

    // Создание задачи
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:inbox,next_action,today,tomorrow,waiting,someday,scheduled,completed',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'project_id' => 'nullable|exists:projects,id',
            'goal_id' => 'nullable|exists:goals,id',
            'life_sphere_id' => 'nullable|exists:life_spheres,id',
            'context_id' => 'nullable|exists:contexts,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_time' => 'nullable|date_format:H:i,H:i:s',
            'end_time' => 'nullable|date_format:H:i,H:i:s',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $task = $this->taskService->createTask($request->user(), $validated, Auth::id());

        return ApiResponse::success($task, 'Задача создана', 201);
    }

    // Получение задачи
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        $task->load([
            'project', 'context', 'assignee', 'tags', 'comments.user',
            'attachments', 'subtasks', 'contacts', 'lifeSphere',
            'assignees', 'watchers', 'checklistItems',
            'creator:id,name',
        ]);
        return ApiResponse::success($task, 'Задача получена');
    }

    // Обновление задачи
    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:inbox,next_action,today,tomorrow,waiting,someday,scheduled,completed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'project_id' => 'nullable|exists:projects,id',
            'goal_id' => 'nullable|exists:goals,id',
            'life_sphere_id' => 'nullable|exists:life_spheres,id',
            'context_id' => 'nullable|exists:contexts,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_time' => 'nullable|date_format:H:i,H:i:s',
            'end_time' => 'nullable|date_format:H:i,H:i:s',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:contacts,id',
            'watcher_ids' => 'nullable|array',
            'watcher_ids.*' => 'exists:contacts,id',
            'checklist' => 'nullable|array',
            'checklist.*.id' => 'nullable|integer',
            'checklist.*.text' => 'required|string|max:500',
            'checklist.*.is_done' => 'sometimes|boolean',
            'checklist.*.position' => 'sometimes|integer',
        ]);

        $assigneeIds = $validated['assignee_ids'] ?? null;
        $watcherIds = $validated['watcher_ids'] ?? null;
        $checklist = $validated['checklist'] ?? null;
        unset($validated['assignee_ids'], $validated['watcher_ids'], $validated['checklist']);

        // Snapshot before update for notification diffing
        $oldAttributes = $task->getAttributes();
        $oldAssigneeIds = $assigneeIds !== null
            ? $task->assignees()->pluck('contacts.id')->sort()->values()->all()
            : null;
        $oldWatcherIds = $watcherIds !== null
            ? $task->watchers()->pluck('contacts.id')->sort()->values()->all()
            : null;

        try {
            $task = $this->taskService->updateTask($task, $validated);

            if ($assigneeIds !== null || $watcherIds !== null) {
                $sync = [];
                foreach ($assigneeIds ?? [] as $id) {
                    $sync[$id] = ['role' => 'assignee'];
                }
                foreach ($watcherIds ?? [] as $id) {
                    if (!isset($sync[$id])) {
                        $sync[$id] = ['role' => 'watcher'];
                    }
                }
                $task->contacts()->sync($sync);
            }

            if ($checklist !== null) {
                $existingIds = $task->checklistItems()->pluck('id')->all();
                $keepIds = [];
                foreach (array_values($checklist) as $index => $item) {
                    $id = $item['id'] ?? null;
                    $payload = [
                        'text' => $item['text'],
                        'is_done' => (bool) ($item['is_done'] ?? false),
                        'position' => $item['position'] ?? $index,
                    ];
                    if ($id && in_array($id, $existingIds)) {
                        $task->checklistItems()->where('id', $id)->update($payload);
                        $keepIds[] = $id;
                    } else {
                        $new = $task->checklistItems()->create($payload);
                        $keepIds[] = $new->id;
                    }
                }
                $task->checklistItems()->whereNotIn('id', $keepIds)->delete();
            }

            $task->load([
                'assignees', 'watchers', 'checklistItems', 'attachments', 'contacts',
                'creator:id,name', 'project:id,name', 'lifeSphere:id,name,color',
            ]);

            $this->telegram->notifyTaskParticipants($task, auth()->user(), 'updated');

            // Schedule push notifications
            $notifyService = app(\App\Services\TaskNotificationService::class);
            $notifyService->scheduleNotification($task, $oldAttributes, $oldAssigneeIds, $oldWatcherIds, $request->user());

            // Immediate notification for added/removed assignees/watchers
            if ($assigneeIds !== null || $watcherIds !== null) {
                $getLinkedUserIds = fn ($contactIds) => \App\Models\Contact::whereIn('id', $contactIds)
                    ->whereNotNull('contact_user_id')
                    ->pluck('contact_user_id')->all();

                $newAssigneeContactIds = $oldAssigneeIds !== null
                    ? array_diff($task->assignees()->pluck('contacts.id')->all(), $oldAssigneeIds) : [];
                $removedAssigneeContactIds = $oldAssigneeIds !== null
                    ? array_diff($oldAssigneeIds, $task->assignees()->pluck('contacts.id')->all()) : [];
                $newWatcherContactIds = $oldWatcherIds !== null
                    ? array_diff($task->watchers()->pluck('contacts.id')->all(), $oldWatcherIds) : [];
                $removedWatcherContactIds = $oldWatcherIds !== null
                    ? array_diff($oldWatcherIds, $task->watchers()->pluck('contacts.id')->all()) : [];

                $notifyService->notifyRoleChanges(
                    $task,
                    $getLinkedUserIds($newAssigneeContactIds),
                    $getLinkedUserIds($newWatcherContactIds),
                    $getLinkedUserIds($removedAssigneeContactIds),
                    $getLinkedUserIds($removedWatcherContactIds),
                    $request->user(),
                );
            }

            return ApiResponse::success($task, 'Задача обновлена');
        } catch (\Exception $e) {
            \Log::error('Error updating task: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Ошибка при обновлении задачи: ' . $e->getMessage(), 500);
        }
    }

    // Удаление задачи
    // Очистка пустых черновиков задач текущего пользователя
    public function cleanupEmpty(Request $request): JsonResponse
    {
        $user = $request->user();
        $workspaceId = $user->defaultWorkspace()->id;

        // Чистим брошенные пустые черновики старше 10 минут в своём пространстве.
        $threshold = now()->subMinutes(10);

        $query = Task::where('workspace_id', $workspaceId)
            ->whereNull('completed_at')
            ->where('created_at', '<', $threshold)
            ->where(function ($q) {
                $q->whereNull('title')->orWhere('title', '')->orWhere('title', 'Без названия');
            })
            ->where(function ($q) {
                $q->whereNull('description')->orWhere('description', '');
            })
            ->whereDoesntHave('checklistItems')
            ->whereDoesntHave('attachments')
            ->whereDoesntHave('contacts')
            ->whereDoesntHave('comments');

        $deleted = $query->count();
        $query->delete();

        return ApiResponse::success(['deleted' => $deleted], 'Очищено');
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);
        $task->delete();
        return ApiResponse::success(null, 'Задача удалена');
    }

    // Завершение задачи
    public function complete(Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $task = $this->taskService->completeTask($task, auth()->id());
        $this->telegram->notifyTaskParticipants($task, auth()->user(), 'completed');
        return ApiResponse::success($task, 'Задача завершена');
    }

    // Возврат задачи в работу
    public function uncomplete(Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        $task = $this->taskService->uncompleteTask($task);
        return ApiResponse::success($task, 'Задача возвращена в работу');
    }

    // Смена статуса
    public function move(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => 'required|in:inbox,next_action,today,tomorrow,waiting,someday,scheduled,completed',
        ]);

        $task = $this->taskService->changeStatus($task, $validated['status'], auth()->id());
        return ApiResponse::success($task, 'Статус задачи изменен');
    }

    // Назначение задачи
    public function assign(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        $task = $this->taskService->assignTask($task, $validated['user_id'] ?? null, auth()->id());
        return ApiResponse::success($task, 'Задача назначена');
    }
}
