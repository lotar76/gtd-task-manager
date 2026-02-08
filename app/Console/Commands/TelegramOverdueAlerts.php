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
        if (!config('services.telegram.bot_token')) {
            $this->warn('Telegram bot token not configured');
            return Command::SUCCESS;
        }

        $now = Carbon::now('Europe/Moscow');
        $today = $now->format('Y-m-d');

        $subscriptions = TelegramSubscription::where('is_active', true)
            ->where('notify_overdue', true)
            ->whereNotNull('chat_id')
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;
            $workspaces = $user->allWorkspaces();

            $allOverdueTasks = collect();

            foreach ($workspaces as $workspace) {
                $overdueTasks = $workspace->tasks()
                    ->with(['project'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', $today)
                    ->whereNotIn('status', ['completed'])
                    ->where(function ($q) use ($user) {
                        $q->where('assigned_to', $user->id)
                          ->orWhere('created_by', $user->id);
                    })
                    ->orderBy('due_date', 'asc')
                    ->get()
                    ->each(fn ($task) => $task->_workspace_name = $workspace->name);

                $allOverdueTasks = $allOverdueTasks->merge($overdueTasks);
            }

            if ($allOverdueTasks->isEmpty()) {
                continue;
            }

            $showWorkspaceName = $workspaces->count() > 1;

            $text = "<b>⚠️ Просроченные задачи ({$allOverdueTasks->count()}):</b>\n\n";

            foreach ($allOverdueTasks->take(10) as $task) {
                $days = Carbon::parse($task->due_date)->diffInDays($now);
                $line = $telegramService->formatTaskLine($task, true);
                if ($showWorkspaceName) {
                    $line .= "\n     📂 {$task->_workspace_name}";
                }
                $text .= "• {$line}\n  ⏰ просрочена {$days} дн.\n\n";
            }

            if ($allOverdueTasks->count() > 10) {
                $text .= "...и ещё " . ($allOverdueTasks->count() - 10) . " задач";
            }

            $telegramService->sendMessage($subscription->chat_id, $text);
            $sent++;
        }

        $this->info("Отправлено уведомлений о просроченных: {$sent}");
        return Command::SUCCESS;
    }
}
