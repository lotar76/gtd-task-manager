<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskChanged extends Notification
{
    public function __construct(
        private readonly Task $task,
        private readonly string $changerName,
        private readonly array $changes,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->buildTitle())
            ->body($this->buildBody())
            ->icon('/android-chrome-192x192.png')
            ->badge('/favicon-96x96.png')
            ->tag('task-changed-' . $this->task->id)
            ->data([
                'url' => '/tasks/' . $this->task->id,
            ]);
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'task_changed',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'changer_name' => $this->changerName,
            'changes' => $this->changes,
        ];
    }

    private function buildTitle(): string
    {
        return $this->task->title ?? 'Задача';
    }

    private function buildBody(): string
    {
        $parts = [];

        foreach ($this->changes as $change) {
            $parts[] = $change;
        }

        $summary = implode(', ', $parts);

        return "{$this->changerName}: {$summary}";
    }
}
