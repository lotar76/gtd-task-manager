<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Context;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {
    }

    // Список задач
    public function index(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $query = $workspace->tasks()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        // Фильтры
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('my_tasks') && $request->my_tasks) {
            $query->where('assigned_to', Auth::id());
        }

        $tasks = $query->orderBy('position')->get();

        return ApiResponse::success($tasks, 'Список задач получен');
    }

    // GTD: Inbox (входящие)
    public function inbox(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getInbox($workspace, $userId);

        return ApiResponse::success($tasks, 'Входящие задачи получены');
    }

    // GTD: Next Actions (следующие действия)
    public function nextActions(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getNextActions($workspace, $userId);

        return ApiResponse::success($tasks, 'Следующие действия получены');
    }

    // GTD: Waiting (ожидание)
    public function waiting(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getWaiting($workspace, $userId);

        return ApiResponse::success($tasks, 'Задачи в ожидании получены');
    }

    // GTD: Someday (когда-нибудь)
    public function someday(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getSomeday($workspace, $userId);

        return ApiResponse::success($tasks, 'Задачи "Когда-нибудь" получены');
    }

    // Задачи на сегодня
    public function today(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getToday($workspace, $userId);

        return ApiResponse::success($tasks, 'Задачи на сегодня получены');
    }

    // Задачи на завтра
    public function tomorrow(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getTomorrow($workspace, $userId);

        return ApiResponse::success($tasks, 'Задачи на завтра получены');
    }

    // Мои задачи
    public function myTasks(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $tasks = $workspace->tasks()
            ->where('assigned_to', Auth::id())
            ->whereNotIn('status', ['completed'])
            ->with(['workspace', 'project', 'context', 'tags'])
            ->orderBy('due_date', 'asc')
            ->get();

        return ApiResponse::success($tasks, 'Мои задачи получены');
    }

    // Календарь задач
    public function calendar(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'my_tasks' => 'boolean',
        ]);

        $userId = $request->my_tasks ? Auth::id() : null;
        $tasks = $this->taskService->getCalendarTasks(
            $workspace,
            $validated['start_date'],
            $validated['end_date'],
            $userId
        );

        return ApiResponse::success($tasks, 'Задачи для календаря получены');
    }

    // Получение счетчиков задач для навигации
    public function counts(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $userId = $request->my_tasks ? Auth::id() : null;
        $counts = $this->taskService->getTaskCounts($workspace, $userId);

        return ApiResponse::success($counts, 'Счетчики задач получены');
    }

    // Создание задачи
    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('createContent', $workspace);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:inbox,next_action,today,tomorrow,waiting,someday,completed',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'workspace_id' => 'nullable|exists:workspaces,id',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:contexts,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_time' => 'nullable|date_format:H:i',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Определяем целевой workspace (из формы или из URL)
        $targetWorkspaceId = $validated['workspace_id'] ?? $workspace->id;
        
        // Проверка доступа к целевому workspace
        if (isset($validated['workspace_id']) && $validated['workspace_id'] !== $workspace->id) {
            $targetWorkspace = Workspace::find($validated['workspace_id']);
            if (!$targetWorkspace || !$targetWorkspace->hasMember(Auth::id())) {
                return ApiResponse::error('У вас нет доступа к выбранному workspace', 403);
            }
        }

        // Проверка принадлежности project к workspace
        if (isset($validated['project_id'])) {
            $project = Project::find($validated['project_id']);
            if (!$project || $project->workspace_id !== $targetWorkspaceId) {
                return ApiResponse::error('Проект не принадлежит выбранному workspace', 422);
            }
        }

        // Проверка принадлежности context к workspace
        if (isset($validated['context_id'])) {
            $context = Context::find($validated['context_id']);
            if (!$context || $context->workspace_id !== $targetWorkspaceId) {
                return ApiResponse::error('Контекст не принадлежит выбранному workspace', 422);
            }
        }

        // Проверка принадлежности tags к workspace
        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $tags = Tag::whereIn('id', $validated['tags'])->get();
            foreach ($tags as $tag) {
                if ($tag->workspace_id !== $targetWorkspaceId) {
                    return ApiResponse::error('Один или несколько тегов не принадлежат выбранному workspace', 422);
                }
            }
        }

        // Проверка что assigned_to является участником workspace
        if (isset($validated['assigned_to'])) {
            $checkWorkspace = isset($validated['workspace_id']) 
                ? Workspace::find($validated['workspace_id']) 
                : $workspace;
            
            if (!$checkWorkspace->hasMember($validated['assigned_to'])) {
                return ApiResponse::error('Пользователь не является участником выбранного workspace', 422);
            }
        }

        $task = $this->taskService->createTask($workspace, $validated, Auth::id());

        return ApiResponse::success($task, 'Задача создана', 201);
    }

    // Получение задачи
    public function show(Workspace $workspace, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['workspace', 'project', 'context', 'assignee', 'tags', 'comments.user', 'attachments', 'subtasks']);

        return ApiResponse::success($task, 'Задача получена');
    }

    // Обновление задачи
    public function update(Request $request, Workspace $workspace, Task $task): JsonResponse
    {
        $task->load('workspace');
        
        // Проверяем, что задача принадлежит workspace
        if ($task->workspace_id !== $workspace->id) {
            return ApiResponse::error('Задача не принадлежит данному workspace', 403);
        }
        
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:inbox,next_action,today,tomorrow,waiting,someday,completed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:contexts,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_time' => 'nullable|date_format:H:i',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Проверка принадлежности project к workspace
        if (isset($validated['project_id'])) {
            $project = Project::find($validated['project_id']);
            if (!$project || $project->workspace_id !== $workspace->id) {
                return ApiResponse::error('Проект не принадлежит данному workspace', 422);
            }
        }

        // Проверка принадлежности context к workspace
        if (isset($validated['context_id'])) {
            $context = Context::find($validated['context_id']);
            if (!$context || $context->workspace_id !== $workspace->id) {
                return ApiResponse::error('Контекст не принадлежит данному workspace', 422);
            }
        }

        // Проверка принадлежности tags к workspace
        if (isset($validated['tags']) && !empty($validated['tags'])) {
            $tags = Tag::whereIn('id', $validated['tags'])->get();
            foreach ($tags as $tag) {
                if ($tag->workspace_id !== $workspace->id) {
                    return ApiResponse::error('Один или несколько тегов не принадлежат данному workspace', 422);
                }
            }
        }

        // Проверка что assigned_to является участником workspace
        if (isset($validated['assigned_to'])) {
            if (!$workspace->hasMember($validated['assigned_to'])) {
                return ApiResponse::error('Пользователь не является участником workspace', 422);
            }
        }

        try {
        $task = $this->taskService->updateTask($task, $validated);
        return ApiResponse::success($task, 'Задача обновлена');
        } catch (\Exception $e) {
            \Log::error('Error updating task: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'workspace_id' => $workspace->id,
                'data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Ошибка при обновлении задачи: ' . $e->getMessage(), 500);
        }
    }

    // Удаление задачи
    public function destroy(Workspace $workspace, Task $task): JsonResponse
    {
        $task->load('workspace');
        $this->authorize('delete', $task);

        $task->delete();

        return ApiResponse::success(null, 'Задача удалена');
    }

    // Завершение задачи
    public function complete(Workspace $workspace, Task $task): JsonResponse
    {
        $task->load('workspace');
        $this->authorize('update', $task);

        $task = $this->taskService->completeTask($task);

        return ApiResponse::success($task, 'Задача завершена');
    }

    // Возврат задачи в работу
    public function uncomplete(Workspace $workspace, Task $task): JsonResponse
    {
        $task->load('workspace');
        $this->authorize('update', $task);

        $task = $this->taskService->uncompleteTask($task);

        return ApiResponse::success($task, 'Задача возвращена в работу');
    }

    // Смена статуса
    public function move(Request $request, Workspace $workspace, Task $task): JsonResponse
    {
        $task->load('workspace');
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => 'required|in:inbox,next_action,today,tomorrow,waiting,someday,completed',
        ]);

        $task = $this->taskService->changeStatus($task, $validated['status']);

        return ApiResponse::success($task, 'Статус задачи изменен');
    }

    // Назначение задачи
    public function assign(Request $request, Workspace $workspace, Task $task): JsonResponse
    {
        $task->load('workspace');
        $this->authorize('assign', $task);

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Проверка что user_id является участником workspace
        if (isset($validated['user_id']) && $validated['user_id'] !== null) {
            if (!$workspace->hasMember($validated['user_id'])) {
                return ApiResponse::error('Пользователь не является участником workspace', 422);
            }
        }

        $task = $this->taskService->assignTask($task, $validated['user_id'] ?? null);

        return ApiResponse::success($task, 'Задача назначена');
    }
}

