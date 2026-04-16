<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    private TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    private function userTasksQuery(User $user)
    {
        $workspaceId = $user->defaultWorkspace()->id;
        $userId = $user->id;
        return Task::where(function ($q) use ($workspaceId, $userId) {
            $q->where('workspace_id', $workspaceId)
              ->orWhereHas('contacts', fn ($c) => $c->where('contacts.contact_user_id', $userId));
        });
    }

    // GTD: Входящие (inbox)
    public function getInbox(User $user, ?int $userId = null): Collection
    {
        $query = $this->userTasksQuery($user)
            ->inbox()
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // GTD: Следующие действия (next_action)
    public function getNextActions(User $user, ?int $userId = null): Collection
    {
        $query = $this->userTasksQuery($user)
            ->nextAction()
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    // GTD: Ожидание (waiting) + задачи, в которых назначен другой исполнитель
    public function getWaiting(User $user, ?int $userId = null): Collection
    {
        $me = $user->id;
        $query = $this->userTasksQuery($user)
            ->whereNull('completed_at')
            ->where(function ($q) use ($me) {
                $q->where('status', 'waiting')
                  ->orWhereHas('contacts', function ($c) use ($me) {
                      $c->where('task_contact.role', 'assignee')
                        ->where('contacts.contact_user_id', '!=', $me);
                  });
            })
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // GTD: Когда-нибудь (someday)
    public function getSomeday(User $user, ?int $userId = null): Collection
    {
        $query = $this->userTasksQuery($user)
            ->someday()
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // Задачи на сегодня
    public function getToday(User $user, ?int $userId = null): Collection
    {
        $query = $this->userTasksQuery($user)
            ->today()
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('priority', 'desc')->get();
    }

    // Задачи на завтра
    public function getTomorrow(User $user, ?int $userId = null): Collection
    {
        $query = $this->userTasksQuery($user)
            ->where('status', 'tomorrow')
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('priority', 'desc')->get();
    }

    // Просроченные задачи
    public function getOverdue(User $user, ?int $userId = null): Collection
    {
        $query = $this->userTasksQuery($user)
            ->overdue()
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // Предстоящие задачи (на неделю)
    public function getUpcoming(User $user, ?int $userId = null): Collection
    {
        $query = $this->userTasksQuery($user)
            ->upcoming()
            ->whereNull('completed_at')
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // Создание задачи
    public function createTask(User $user, array $data, int $userId): Task
    {
        // Берём первый workspace пользователя для workspace_id (legacy)
        $workspace = $user->defaultWorkspace();
        $data['workspace_id'] = $workspace->id;
        $data['created_by'] = $userId;
        $data['title'] = $data['title'] ?? '';

        // Если не указан assigned_to, назначаем создателя
        if (!isset($data['assigned_to'])) {
            $data['assigned_to'] = $userId;
        }

        // Автоматическая позиция
        if (!isset($data['position'])) {
            $data['position'] = Task::where('workspace_id', $workspace->id)->max('position') + 1;
        }

        // Извлекаем contact_ids перед созданием задачи
        $contactIds = $data['contact_ids'] ?? [];
        unset($data['contact_ids']);

        $task = Task::create($data);

        // Привязка тегов
        if (isset($data['tags'])) {
            $task->tags()->sync($data['tags']);
        }

        // Привязка контактов
        if (!empty($contactIds)) {
            $task->contacts()->sync(
                collect($contactIds)->mapWithKeys(fn ($id) => [$id => ['role' => 'informed']])->toArray()
            );
        }

        $task->load(['project', 'context', 'assignee', 'tags', 'creator', 'contacts']);

        // Уведомляем
        $this->notifyMembers($task, $userId, 'created');

        return $task;
    }

    // Обновление задачи
    public function updateTask(Task $task, array $data): Task
    {
        // Если устанавливается due_date, проверяем нужно ли изменить статус
        if (isset($data['due_date']) && $data['due_date'] && !isset($data['status'])) {
            $dueDate = \Carbon\Carbon::parse($data['due_date'])->startOfDay();
            $today = \Carbon\Carbon::today();
            $tomorrow = \Carbon\Carbon::tomorrow();

            if ($dueDate->isSameDay($today) && $task->status !== 'completed') {
                $data['status'] = 'today';
            } elseif ($dueDate->isSameDay($tomorrow) && $task->status !== 'completed') {
                $data['status'] = 'tomorrow';
            } elseif ($task->status !== 'completed' &&
                    !in_array($task->status, ['today', 'tomorrow', 'completed'])) {
                $data['status'] = 'scheduled';
            }
        }

        // Извлекаем contact_ids перед обновлением
        $contactIds = $data['contact_ids'] ?? null;
        unset($data['contact_ids']);

        $task->update($data);

        // Привязка тегов
        if (isset($data['tags'])) {
            $task->tags()->sync($data['tags']);
        }

        // Привязка контактов
        if ($contactIds !== null) {
            $task->contacts()->sync(
                collect($contactIds)->mapWithKeys(fn ($id) => [$id => ['role' => 'informed']])->toArray()
            );
        }

        // Загружаем связи
        $relations = ['tags', 'contacts'];
        if ($task->project_id) $relations[] = 'project';
        if ($task->context_id) $relations[] = 'context';
        if ($task->assigned_to) $relations[] = 'assignee';
        if ($task->created_by) $relations[] = 'creator';

        return $task->load($relations);
    }

    // Смена статуса задачи
    public function changeStatus(Task $task, string $status, ?int $actorUserId = null): Task
    {
        $updateData = ['status' => $status];

        if ($status === 'today') {
            $updateData['due_date'] = \Carbon\Carbon::today()->format('Y-m-d');
        } elseif ($status === 'tomorrow') {
            $updateData['due_date'] = \Carbon\Carbon::tomorrow()->format('Y-m-d');
        }

        $task->update($updateData);

        if ($status === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);

            if ($actorUserId) {
                $this->notifyMembers($task, $actorUserId, 'completed');
            }
        }

        return $task->fresh();
    }

    // Завершение задачи
    public function completeTask(Task $task, ?int $actorUserId = null): Task
    {
        $task->update(['completed_at' => now()]);
        $task = $task->fresh();

        if ($actorUserId) {
            $this->notifyMembers($task, $actorUserId, 'completed');
        }

        return $task;
    }

    // Возврат задачи в работу
    public function uncompleteTask(Task $task): Task
    {
        $task->update(['completed_at' => null]);
        return $task->fresh();
    }

    // Назначение задачи
    public function assignTask(Task $task, ?int $userId, ?int $actorUserId = null): Task
    {
        $oldAssignee = $task->assigned_to;
        $task->update(['assigned_to' => $userId]);
        $task = $task->fresh();

        if ($userId && $userId !== $oldAssignee && $actorUserId) {
            $this->notifyMembers($task, $actorUserId, 'assigned');
        }

        return $task;
    }

    /**
     * Отправить Telegram-уведомление участникам workspace.
     */
    private function notifyMembers(Task $task, int $actorUserId, string $action): void
    {
        try {
            $task->loadMissing('workspace');
            $actor = User::find($actorUserId);
            if ($actor) {
                $this->telegramService->notifyWorkspaceMembers($task, $actor, $action);
            }
        } catch (\Throwable $e) {
            Log::error("Telegram notification error: {$e->getMessage()}");
        }
    }

    // Получить задачи для календаря
    public function getCalendarTasks(User $user, string $startDate, string $endDate, ?int $userId = null)
    {
        $today = \Carbon\Carbon::today()->toDateString();
        $tomorrow = \Carbon\Carbon::tomorrow()->toDateString();
        $todayInRange = $today >= $startDate && $today <= $endDate;
        $tomorrowInRange = $tomorrow >= $startDate && $tomorrow <= $endDate;

        $query = $this->userTasksQuery($user)
            ->whereNull('completed_at')
            ->where(function ($q) use ($startDate, $endDate, $todayInRange, $tomorrowInRange) {
                $q->whereBetween('due_date', [$startDate, $endDate]);
                if ($todayInRange) {
                    $q->orWhere(fn ($qq) => $qq->where('status', 'today')->whereNull('due_date'));
                }
                if ($tomorrowInRange) {
                    $q->orWhere(fn ($qq) => $qq->where('status', 'tomorrow')->whereNull('due_date'));
                }
            })
            ->with(['project', 'context', 'assignee', 'tags', 'contacts']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // Получить счетчики задач для навигации
    public function getTaskCounts(User $user, ?int $userId = null): array
    {
        $query = $this->userTasksQuery($user)->whereNull('completed_at');

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return [
            'inbox' => (clone $query)->where('status', 'inbox')->count(),
            'next_actions' => (clone $query)->where('status', 'next_action')->count(),
            'today' => (clone $query)->where('status', 'today')->count(),
            'tomorrow' => (clone $query)->where('status', 'tomorrow')->count(),
            'waiting' => (clone $query)->where(function ($q) use ($user) {
                $q->where('status', 'waiting')
                  ->orWhereHas('contacts', function ($c) use ($user) {
                      $c->where('task_contact.role', 'assignee')
                        ->where('contacts.contact_user_id', '!=', $user->id);
                  });
            })->count(),
            'someday' => (clone $query)->where('status', 'someday')->count(),
            'scheduled' => (clone $query)->where('status', 'scheduled')->count(),
        ];
    }
}
