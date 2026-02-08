<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TelegramSubscription;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TelegramTaskReminders extends Command
{
    protected $signature = 'telegram:task-reminders';
    protected $description = 'Отправить напоминания о предстоящих задачах подписчикам Telegram';

    public function handle(TelegramService $telegramService): int
    {
        if (!config('services.telegram.bot_token')) {
            $this->warn('Telegram bot token not configured');
            return Command::SUCCESS;
        }

        $now = Carbon::now('Europe/Moscow');
        $today = $now->format('Y-m-d');

        $subscriptions = TelegramSubscription::where('is_active', true)
            ->where('notify_reminders', true)
            ->whereNotNull('chat_id')
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;
            $workspaces = $user->allWorkspaces();
            $reminderMinutes = $subscription->reminder_minutes_before;
            $reminderTime = $now->copy()->addMinutes($reminderMinutes)->format('H:i');

            $showWorkspaceName = $workspaces->count() > 1;

            foreach ($workspaces as $workspace) {
                $tasks = $workspace->tasks()
                    ->with(['project', 'context'])
                    ->where('due_date', $today)
                    ->whereNotIn('status', ['completed'])
                    ->whereNotNull('estimated_time')
                    ->where(function ($q) use ($user) {
                        $q->where('assigned_to', $user->id)
                          ->orWhere('created_by', $user->id);
                    })
                    ->get();

                foreach ($tasks as $task) {
                    $taskTime = substr($task->estimated_time, 0, 5);

                    if ($taskTime === $reminderTime) {
                        $text = "🔔 <b>Напоминание</b>\n"
                            . "Через {$reminderMinutes} мин.\n\n"
                            . $telegramService->formatTask($task);

                        if ($showWorkspaceName) {
                            $text .= "\n📂 {$workspace->name}";
                        }

                        $telegramService->sendMessageWithKeyboard(
                            $subscription->chat_id,
                            $text,
                            [[['text' => '✅ Закрыть задачу', 'callback_data' => "done:{$task->id}"]]]
                        );
                        $sent++;
                    }
                }
            }
        }

        $this->info("Отправлено напоминаний: {$sent}");
        return Command::SUCCESS;
    }
}
