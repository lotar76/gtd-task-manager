<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskReminder extends Notification
{
    public function __construct(
        private readonly Task $task,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(mixed $notifiable, mixed $notification): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Напоминание о задаче')
            ->body($this->task->title)
            ->icon('/android-chrome-192x192.png')
            ->badge('/favicon-96x96.png')
            ->tag('task-reminder-' . $this->task->id)
            ->data([
                'url' => '/tasks/' . $this->task->id,
            ]);
    }
}
