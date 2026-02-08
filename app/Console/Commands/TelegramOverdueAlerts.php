<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TelegramSubscription;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TelegramOverdueAlerts extends Command
{
    protected $signature = 'telegram:overdue-alerts';
    protected $description = 'Отправить уведомления о просроченных задачах подписчикам Telegram';

    public function handle(TelegramService $telegramService): int
    {
        $now = Carbon::now('Europe/Moscow');
        $today = $now->format('Y-m-d');

        $subscriptions = TelegramSubscription::where('is_active', true)
            ->where('notify_overdue', true)
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

            $overdueTasks = $workspace->tasks()
                ->whereNotNull('due_date')
                ->where('due_date', '<', $today)
                ->whereNotIn('status', ['completed'])
                ->where(function ($q) use ($userId) {
                    $q->where('assigned_to', $userId)
                      ->orWhere('created_by', $userId);
                })
                ->orderBy('due_date', 'asc')
                ->get();

            if ($overdueTasks->isEmpty()) {
                continue;
            }

            $text = "<b>Просроченные задачи ({$overdueTasks->count()}):</b>\n\n";

            foreach ($overdueTasks->take(10) as $task) {
                $days = Carbon::parse($task->due_date)->diffInDays($now);
                $text .= "- <b>{$task->title}</b> (просрочена {$days} дн.)\n";
            }

            if ($overdueTasks->count() > 10) {
                $text .= "\n...и ещё " . ($overdueTasks->count() - 10) . " задач";
            }

            $telegramService->sendMessage($setting->bot_token, $subscription->chat_id, $text);
            $sent++;
        }

        $this->info("Отправлено уведомлений о просроченных: {$sent}");
        return Command::SUCCESS;
    }
}
