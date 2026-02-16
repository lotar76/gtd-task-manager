<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AiMirrorCache;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
    /**
     * Инвалидация AI кэша при изменении задачи
     */
    private function invalidateAiCache(Task $task): void
    {
        if (!$task->workspace_id) {
            return;
        }

        // Определяем какие периоды затронуты
        $periodsToInvalidate = ['day']; // День всегда инвалидируем

        // Если есть due_date или completed_at - проверяем неделю/месяц
        $taskDate = $task->due_date ? Carbon::parse($task->due_date) :
                    ($task->completed_at ? Carbon::parse($task->completed_at) : Carbon::today());

        // Инвалидируем для текущей недели если задача в текущей неделе
        if ($taskDate->isCurrentWeek()) {
            $periodsToInvalidate[] = 'week';
        }

        // Инвалидируем для текущего месяца если задача в текущем месяце
        if ($taskDate->isCurrentMonth()) {
            $periodsToInvalidate[] = 'month';
        }

        // Инвалидируем для текущего года если задача в текущем году
        if ($taskDate->isCurrentYear()) {
            $periodsToInvalidate[] = 'year';
        }

        $updated = 0;
        foreach ($periodsToInvalidate as $period) {
            $count = AiMirrorCache::where('workspace_id', $task->workspace_id)
                ->where('period', $period)
                ->update(['is_stale' => true]);
            $updated += $count;
        }

        if ($updated > 0) {
            Log::info('TaskObserver: AI cache marked as stale', [
                'workspace_id' => $task->workspace_id,
                'task_id' => $task->id,
                'periods' => $periodsToInvalidate,
                'updated_entries' => $updated,
            ]);
        }
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->invalidateAiCache($task);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // Инвалидируем только если изменились значимые поля
        if ($task->wasChanged(['status', 'due_date', 'completed_at', 'life_sphere_id', 'project_id', 'goal_id'])) {
            $this->invalidateAiCache($task);
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        $this->invalidateAiCache($task);
    }
}
