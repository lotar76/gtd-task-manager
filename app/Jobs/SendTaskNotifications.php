<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendTaskNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $taskId,
        private readonly int $changerId,
        private readonly string $cacheKey,
        private readonly array $recipientIds,
    ) {}

    public function handle(): void
    {
        $changes = Cache::pull($this->cacheKey);

        if (empty($changes)) {
            return;
        }

        $task = Task::find($this->taskId);
        if (!$task) return;

        $changer = User::find($this->changerId);
        if (!$changer) return;

        foreach ($this->recipientIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            try {
                $user->notify(new TaskChanged($task, $changer->name, $changes));
            } catch (\Throwable $e) {
                Log::warning("Failed to notify user {$userId} about task {$this->taskId}: " . $e->getMessage());
            }
        }
    }
}
