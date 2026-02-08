<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TelegramSubscription;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TelegramMorningDigest extends Command
{
    protected $signature = 'telegram:morning-digest';
    protected $description = 'Отправить утренний дайджест задач подписчикам Telegram';

    public function handle(TelegramService $telegramService): int
    {
        $now = Carbon::now('Europe/Moscow');
        $currentTime = $now->format('H:i');

        $subscriptions = TelegramSubscription::where('is_active', true)
            ->where('notify_morning_digest', true)
            ->where('morning_digest_time', $currentTime)
            ->whereNotNull('chat_id')
            ->with(['workspace.telegramSetting', 'user'])
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $setting = $subscription->workspace->telegramSetting;
            if (!$setting || !$setting->is_active) {
                continue;
            }

            $workspace = $subscription->workspace;
            $userId = $subscription->user_id;

            // Задачи на сегодня
            $todayTasks = $workspace->tasks()
                ->where('status', 'today')
                ->where(function ($q) use ($userId) {
                    $q->where('assigned_to', $userId)
                      ->orWhere('created_by', $userId);
                })
                ->orderBy('priority', 'desc')
                ->get();

            // Просроченные задачи
            $overdueTasks = $workspace->tasks()
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now->format('Y-m-d'))
                ->whereNotIn('status', ['completed'])
                ->where(function ($q) use ($userId) {
                    $q->where('assigned_to', $userId)
                      ->orWhere('created_by', $userId);
                })
                ->get();

            $text = "<b>Доброе утро, {$subscription->user->name}!</b>\n\n";

            if ($todayTasks->isNotEmpty()) {
                $text .= "<b>Задачи на сегодня ({$todayTasks->count()}):</b>\n";
                foreach ($todayTasks as $i => $task) {
                    $priority = $task->priority ? " [{$task->priority}]" : '';
                    $text .= ($i + 1) . ". {$task->title}{$priority}\n";
                }
            } else {
                $text .= "На сегодня задач нет.\n";
            }

            if ($overdueTasks->isNotEmpty()) {
                $text .= "\n<b>Просроченные ({$overdueTasks->count()}):</b>\n";
                foreach ($overdueTasks->take(5) as $task) {
                    $days = Carbon::parse($task->due_date)->diffInDays($now);
                    $text .= "- {$task->title} (просрочена {$days} дн.)\n";
                }
                if ($overdueTasks->count() > 5) {
                    $text .= "...и ещё " . ($overdueTasks->count() - 5) . "\n";
                }
            }

            $telegramService->sendMessage($setting->bot_token, $subscription->chat_id, $text);
            $sent++;
        }

        $this->info("Отправлено дайджестов: {$sent}");
        return Command::SUCCESS;
    }
}
