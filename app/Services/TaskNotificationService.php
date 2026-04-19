<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Jobs\SendTaskNotifications;
use Illuminate\Support\Facades\Cache;

class TaskNotificationService
{
    private const LABELS = [
        'status' => 'статус',
        'priority' => 'приоритет',
        'due_date' => 'дата',
        'title' => 'название',
        'description' => 'описание',
        'project_id' => 'поток',
        'goal_id' => 'цель',
        'estimated_time' => 'время начала',
        'end_time' => 'время окончания',
        'assignees' => 'исполнители',
        'watchers' => 'наблюдатели',
        'completed' => 'завершение',
    ];

    /**
     * Detect changes and schedule batched notification.
     *
     * @param Task $task Task after save
     * @param array $oldAttributes Attributes before save
     * @param array|null $oldAssigneeIds Assignee IDs before save
     * @param array|null $oldWatcherIds Watcher IDs before save
     * @param User $changer Who made the change
     */
    public function scheduleNotification(
        Task $task,
        array $oldAttributes,
        ?array $oldAssigneeIds,
        ?array $oldWatcherIds,
        User $changer,
    ): void {
        $changes = $this->detectChanges($task, $oldAttributes, $oldAssigneeIds, $oldWatcherIds);

        if (empty($changes)) {
            return;
        }

        $recipients = $this->getRecipients($task, $changer);

        if (empty($recipients)) {
            return;
        }

        // Batch: accumulate changes in cache, dispatch job with delay
        $cacheKey = "task_notify:{$task->id}:{$changer->id}";
        $existing = Cache::get($cacheKey, []);
        $merged = array_unique(array_merge($existing, $changes));
        Cache::put($cacheKey, $merged, 120); // 2 min TTL

        // Dispatch delayed job (replaces previous one via unique ID)
        SendTaskNotifications::dispatch(
            $task->id,
            $changer->id,
            $cacheKey,
            $recipients,
        )->delay(now()->addSeconds(60))
         ->onQueue('default');
    }

    /**
     * Notify users when they are added as assignee or watcher (immediate).
     */
    public function notifyRoleAdded(
        Task $task,
        array $addedAssigneeUserIds,
        array $addedWatcherUserIds,
        User $changer,
    ): void {
        foreach ($addedAssigneeUserIds as $userId) {
            if ($userId === $changer->id) continue;
            $user = User::find($userId);
            if (!$user) continue;

            $user->notify(new \App\Notifications\TaskChanged(
                $task,
                $changer->name,
                ['назначен исполнителем'],
            ));
        }

        foreach ($addedWatcherUserIds as $userId) {
            if ($userId === $changer->id) continue;
            $user = User::find($userId);
            if (!$user) continue;

            $user->notify(new \App\Notifications\TaskChanged(
                $task,
                $changer->name,
                ['добавлен наблюдателем'],
            ));
        }
    }

    private function detectChanges(
        Task $task,
        array $old,
        ?array $oldAssigneeIds,
        ?array $oldWatcherIds,
    ): array {
        $changes = [];
        $tracked = ['status', 'priority', 'due_date', 'title', 'description', 'project_id', 'goal_id', 'estimated_time', 'end_time'];

        foreach ($tracked as $field) {
            $oldVal = $old[$field] ?? null;
            $newVal = $task->getAttribute($field);

            // Normalize dates for comparison
            if ($oldVal instanceof \DateTimeInterface) $oldVal = $oldVal->format('Y-m-d');
            if ($newVal instanceof \DateTimeInterface) $newVal = $newVal->format('Y-m-d');

            $oldVal = is_null($oldVal) ? '' : (string) $oldVal;
            $newVal = is_null($newVal) ? '' : (string) $newVal;

            if ($oldVal !== $newVal) {
                $changes[] = self::LABELS[$field] ?? $field;
            }
        }

        if ($oldAssigneeIds !== null) {
            $newAssigneeIds = $task->assignees()->pluck('contacts.id')->sort()->values()->all();
            $oldSorted = collect($oldAssigneeIds)->sort()->values()->all();
            if ($oldSorted !== $newAssigneeIds) {
                $changes[] = self::LABELS['assignees'];
            }
        }

        if ($oldWatcherIds !== null) {
            $newWatcherIds = $task->watchers()->pluck('contacts.id')->sort()->values()->all();
            $oldSorted = collect($oldWatcherIds)->sort()->values()->all();
            if ($oldSorted !== $newWatcherIds) {
                $changes[] = self::LABELS['watchers'];
            }
        }

        return $changes;
    }

    /**
     * Get user IDs that should be notified (assignees + watchers linked to users, excluding changer).
     */
    private function getRecipients(Task $task, User $changer): array
    {
        $userIds = [];

        // Creator
        if ($task->created_by && $task->created_by !== $changer->id) {
            $userIds[] = $task->created_by;
        }

        // Contacts linked to users (assignees + watchers)
        $contacts = $task->contacts()
            ->whereNotNull('contacts.contact_user_id')
            ->pluck('contacts.contact_user_id')
            ->all();

        foreach ($contacts as $userId) {
            if ($userId !== $changer->id) {
                $userIds[] = $userId;
            }
        }

        return array_unique($userIds);
    }
}
