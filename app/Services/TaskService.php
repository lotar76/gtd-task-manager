<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TaskService
{
    // GTD: Входящие (inbox)
    public function getInbox(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->inbox()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // GTD: Следующие действия (next_action)
    public function getNextActions(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->nextAction()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();
    }

    // GTD: Ожидание (waiting)
    public function getWaiting(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->waiting()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // GTD: Когда-нибудь (someday)
    public function getSomeday(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->someday()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    // Задачи на сегодня
    public function getToday(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->today()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('priority', 'desc')->get();
    }

    // Задачи на завтра
    public function getTomorrow(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->where('status', 'tomorrow')->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('priority', 'desc')->get();
    }

    // Просроченные задачи
    public function getOverdue(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->overdue()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // Предстоящие задачи (на неделю)
    public function getUpcoming(Workspace $workspace, ?int $userId = null): Collection
    {
        $query = $workspace->tasks()->upcoming()->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // Создание задачи
    public function createTask(Workspace $workspace, array $data, int $userId): Task
    {
        // Используем workspace_id из формы, если передан, иначе из URL
        if (!isset($data['workspace_id'])) {
            $data['workspace_id'] = $workspace->id;
        }
        
        $data['created_by'] = $userId;
        
        // Если не указан assigned_to, назначаем создателя
        if (!isset($data['assigned_to'])) {
            $data['assigned_to'] = $userId;
        }

        // Определяем workspace для позиции
        $targetWorkspace = Workspace::find($data['workspace_id']);
        
        // Автоматическая позиция
        if (!isset($data['position'])) {
            $data['position'] = $targetWorkspace->tasks()->max('position') + 1;
        }

        $task = Task::create($data);

        // Привязка тегов
        if (isset($data['tags'])) {
            $task->tags()->sync($data['tags']);
        }

        return $task->load(['workspace', 'project', 'context', 'assignee', 'tags', 'creator']);
    }

    // Обновление задачи
    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        // Привязка тегов
        if (isset($data['tags'])) {
            $task->tags()->sync($data['tags']);
        }

        // Загружаем связи безопасно, пропуская несуществующие
        $relations = [];
        if ($task->workspace_id) $relations[] = 'workspace';
        if ($task->project_id) $relations[] = 'project';
        if ($task->context_id) $relations[] = 'context';
        if ($task->assigned_to) $relations[] = 'assignee';
        if ($task->created_by) $relations[] = 'creator';
        $relations[] = 'tags'; // Теги всегда могут быть пустыми
        
        return $task->load($relations);
    }

    // Смена статуса задачи
    public function changeStatus(Task $task, string $status): Task
    {
        $task->update(['status' => $status]);

        // Если завершена - ставим дату
        if ($status === 'completed' && !$task->completed_at) {
            $task->update(['completed_at' => now()]);
        }

        // Если снова активна - убираем дату завершения
        if ($status !== 'completed' && $task->completed_at) {
            $task->update(['completed_at' => null]);
        }

        return $task->fresh();
    }

    // Завершение задачи
    public function completeTask(Task $task): Task
    {
        return $this->changeStatus($task, 'completed');
    }

    // Возврат задачи в работу
    public function uncompleteTask(Task $task): Task
    {
        return $this->changeStatus($task, 'next_action');
    }

    // Назначение задачи
    public function assignTask(Task $task, ?int $userId): Task
    {
        $task->update(['assigned_to' => $userId]);
        return $task->fresh();
    }

    // Получить задачи для календаря
    public function getCalendarTasks(Workspace $workspace, string $startDate, string $endDate, ?int $userId = null)
    {
        $query = $workspace->tasks()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->with(['workspace', 'project', 'context', 'assignee', 'tags']);

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }

    // Получить счетчики задач для навигации
    public function getTaskCounts(Workspace $workspace, ?int $userId = null): array
    {
        $query = $workspace->tasks();

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        return [
            'inbox' => (clone $query)->where('status', 'inbox')->count(),
            'next_actions' => (clone $query)->where('status', 'next_action')->count(),
            'today' => (clone $query)->where('status', 'today')->count(),
            'tomorrow' => (clone $query)->where('status', 'tomorrow')->count(),
            'waiting' => (clone $query)->where('status', 'waiting')->count(),
            'someday' => (clone $query)->where('status', 'someday')->count(),
        ];
    }
}

