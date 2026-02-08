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
        if (!config('services.telegram.bot_token')) {
            $this->warn('Telegram bot token not configured');
            return Command::SUCCESS;
        }

        $now = Carbon::now('Europe/Moscow');
        $currentTime = $now->format('H:i');

        $subscriptions = TelegramSubscription::where('is_active', true)
            ->where('notify_morning_digest', true)
            ->where('morning_digest_time', $currentTime)
            ->whereNotNull('chat_id')
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;
            $workspaces = $user->allWorkspaces();

            $allTodayTasks = collect();
            $allOverdueTasks = collect();

            foreach ($workspaces as $workspace) {
                $todayTasks = $workspace->tasks()
                    ->with(['project', 'context'])
                    ->where('status', 'today')
                    ->where(function ($q) use ($user) {
                        $q->where('assigned_to', $user->id)
                          ->orWhere('created_by', $user->id);
                    })
                    ->orderBy('estimated_time', 'asc')
                    ->orderBy('priority', 'desc')
                    ->get()
                    ->each(fn ($task) => $task->_workspace_name = $workspace->name);

                $overdueTasks = $workspace->tasks()
                    ->with(['project'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', $now->format('Y-m-d'))
                    ->whereNotIn('status', ['completed'])
                    ->where(function ($q) use ($user) {
                        $q->where('assigned_to', $user->id)
                          ->orWhere('created_by', $user->id);
                    })
                    ->orderBy('due_date', 'asc')
                    ->get()
                    ->each(fn ($task) => $task->_workspace_name = $workspace->name);

                $allTodayTasks = $allTodayTasks->merge($todayTasks);
                $allOverdueTasks = $allOverdueTasks->merge($overdueTasks);
            }

            $allTodayTasks = $allTodayTasks->sortBy([
                ['estimated_time', 'asc'],
                ['priority', 'desc'],
            ]);

            $showWorkspaceName = $workspaces->count() > 1;

            $text = "<b>☀️ Доброе утро, {$user->name}!</b>\n\n";

            if ($allTodayTasks->isNotEmpty()) {
                $text .= "<b>📋 Задачи на сегодня ({$allTodayTasks->count()}):</b>\n";
                foreach ($allTodayTasks->values() as $i => $task) {
                    $line = $telegramService->formatTaskLine($task);
                    if ($showWorkspaceName) {
                        $line .= "  [{$task->_workspace_name}]";
                    }
                    $text .= ($i + 1) . ". {$line}\n";
                }
            } else {
                $text .= "На сегодня задач нет. 🎉\n";
            }

            if ($allOverdueTasks->isNotEmpty()) {
                $text .= "\n<b>⚠️ Просроченные ({$allOverdueTasks->count()}):</b>\n";
                foreach ($allOverdueTasks->take(5) as $task) {
                    $days = Carbon::parse($task->due_date)->diffInDays($now);
                    $line = $telegramService->formatTaskLine($task, true);
                    if ($showWorkspaceName) {
                        $line .= "  [{$task->_workspace_name}]";
                    }
                    $text .= "- {$line} ({$days} дн.)\n";
                }
                if ($allOverdueTasks->count() > 5) {
                    $text .= "...и ещё " . ($allOverdueTasks->count() - 5) . "\n";
                }
            }

            $telegramService->sendMessage($subscription->chat_id, $text);
            $sent++;
        }

        $this->info("Отправлено дайджестов: {$sent}");
        return Command::SUCCESS;
    }
}
