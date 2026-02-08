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
        $now = Carbon::now('Europe/Moscow');
        $today = $now->format('Y-m-d');

        $subscriptions = TelegramSubscription::where('is_active', true)
            ->where('notify_reminders', true)
            ->whereNotNull('chat_id')
            ->with(['workspace.telegramSetting'])
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $setting = $subscription->workspace->telegramSetting;
            if (!$setting || !$setting->is_active) {
                continue;
            }

            $workspace = $subscription->workspace;
            $userId = $subscription->user_id;
            $reminderMinutes = $subscription->reminder_minutes_before;

            // Ищем задачи на сегодня с estimated_time
            // Напоминаем за N минут до начала
            $reminderTime = $now->copy()->addMinutes($reminderMinutes)->format('H:i');
            $currentMinute = $now->format('H:i');

            $tasks = $workspace->tasks()
                ->where('due_date', $today)
                ->whereNotIn('status', ['completed'])
                ->whereNotNull('estimated_time')
                ->where(function ($q) use ($userId) {
                    $q->where('assigned_to', $userId)
                      ->orWhere('created_by', $userId);
                })
                ->get();

            foreach ($tasks as $task) {
                // estimated_time хранит время начала задачи (HH:MM)
                $taskTime = substr($task->estimated_time, 0, 5);

                if ($taskTime === $reminderTime) {
                    $text = "Напоминание: через {$reminderMinutes} мин.\n"
                        . "<b>{$task->title}</b>";

                    if ($task->description) {
                        $text .= "\n" . mb_substr($task->description, 0, 100);
                    }

                    $telegramService->sendMessage($setting->bot_token, $subscription->chat_id, $text);
                    $sent++;
                }
            }
        }

        $this->info("Отправлено напоминаний: {$sent}");
        return Command::SUCCESS;
    }
}
