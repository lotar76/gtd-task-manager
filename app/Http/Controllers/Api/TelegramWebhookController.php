<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramSubscription;
use App\Services\TaskService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramService $telegramService,
        private TaskService $taskService
    ) {}

    public function handle(Request $request, string $secret): JsonResponse
    {
        $expectedSecret = config('services.telegram.webhook_secret');

        if (!$expectedSecret || $secret !== $expectedSecret) {
            return response()->json(['ok' => false], 404);
        }

        if (!config('services.telegram.bot_token')) {
            return response()->json(['ok' => false], 500);
        }

        $update = $request->all();
        $message = $update['message'] ?? null;

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $message['chat']['id'];
        $text = trim($message['text']);

        if (str_starts_with($text, '/start')) {
            return $this->handleStart($chatId, $text);
        }

        $subscription = TelegramSubscription::where('chat_id', $chatId)
            ->where('is_active', true)
            ->with('user')
            ->first();

        if (!$subscription) {
            $this->telegramService->sendMessage(
                $chatId,
                "Вы не подключены к боту.\nИспользуйте ссылку из приложения для подключения."
            );
            return response()->json(['ok' => true]);
        }

        if ($text === '/today') {
            return $this->handleToday($subscription, $chatId);
        }

        if ($text === '/help') {
            return $this->handleHelp($chatId);
        }

        return $this->handleCreateTask($subscription, $chatId, $text);
    }

    private function handleStart(string $chatId, string $text): JsonResponse
    {
        $parts = explode(' ', $text, 2);
        $linkToken = $parts[1] ?? null;

        if (!$linkToken) {
            $this->telegramService->sendMessage(
                $chatId,
                "Для подключения используйте ссылку из приложения GTD Task Manager.\n\nОткройте настройки Telegram в приложении и нажмите \"Подключить\"."
            );
            return response()->json(['ok' => true]);
        }

        $subscription = TelegramSubscription::where('link_token', $linkToken)->first();

        if (!$subscription) {
            $this->telegramService->sendMessage(
                $chatId,
                "Ссылка недействительна или устарела.\nПопробуйте создать новую ссылку в приложении."
            );
            return response()->json(['ok' => true]);
        }

        $subscription->update([
            'chat_id' => $chatId,
            'is_active' => true,
        ]);

        $user = $subscription->user;
        $workspaces = $user->allWorkspaces();
        $wsNames = $workspaces->pluck('name')->implode(', ');

        $this->telegramService->sendMessage(
            $chatId,
            "Привет, {$user->name}! Вы подключены к GTD Task Manager.\n\n"
            . "Ваши пространства: <b>{$wsNames}</b>\n\n"
            . "Что я умею:\n"
            . "- Отправьте текст — создам задачу во Входящих\n"
            . "- /today — список задач на сегодня (все пространства)\n"
            . "- /help — справка\n\n"
            . "Настройте уведомления в приложении."
        );

        return response()->json(['ok' => true]);
    }

    private function handleToday(TelegramSubscription $subscription, string $chatId): JsonResponse
    {
        $user = $subscription->user;
        $workspaces = $user->allWorkspaces();

        $allTasks = collect();

        foreach ($workspaces as $workspace) {
            $tasks = $workspace->tasks()
                ->with(['project', 'context'])
                ->where('status', 'today')
                ->where(function ($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                      ->orWhere('created_by', $user->id);
                })
                ->orderBy('estimated_time', 'asc')
                ->orderBy('priority', 'desc')
                ->get()
                ->each(function ($task) use ($workspace) {
                    $task->_workspace_name = $workspace->name;
                });

            $allTasks = $allTasks->merge($tasks);
        }

        $allTasks = $allTasks->sortBy([
            ['estimated_time', 'asc'],
            ['priority', 'desc'],
        ]);

        if ($allTasks->isEmpty()) {
            $this->telegramService->sendMessage($chatId, "На сегодня задач нет. 🎉");
            return response()->json(['ok' => true]);
        }

        $showWorkspaceName = $workspaces->count() > 1;

        $text = "<b>📋 Задачи на сегодня ({$allTasks->count()}):</b>\n\n";
        foreach ($allTasks->values() as $i => $task) {
            $line = $this->telegramService->formatTaskLine($task);
            if ($showWorkspaceName) {
                $line .= "  [{$task->_workspace_name}]";
            }
            $text .= ($i + 1) . ". {$line}\n";
        }

        $this->telegramService->sendMessage($chatId, $text);
        return response()->json(['ok' => true]);
    }

    private function handleHelp(string $chatId): JsonResponse
    {
        $text = "<b>GTD Task Manager Bot</b>\n\n"
            . "Команды:\n"
            . "/today — задачи на сегодня (все пространства)\n"
            . "/help — эта справка\n\n"
            . "Отправьте любой текст — и я создам задачу во Входящих.\n\n"
            . "Настройте уведомления в приложении:\n"
            . "- Утренний дайджест\n"
            . "- Напоминания о задачах\n"
            . "- Просроченные задачи";

        $this->telegramService->sendMessage($chatId, $text);
        return response()->json(['ok' => true]);
    }

    private function handleCreateTask(TelegramSubscription $subscription, string $chatId, string $text): JsonResponse
    {
        try {
            $user = $subscription->user;

            $workspace = $subscription->defaultWorkspace
                ?? $user->allWorkspaces()->first();

            if (!$workspace) {
                $this->telegramService->sendMessage(
                    $chatId,
                    "У вас нет ни одного пространства. Создайте пространство в приложении."
                );
                return response()->json(['ok' => true]);
            }

            $task = $this->taskService->createTask($workspace, [
                'title' => $text,
                'status' => 'inbox',
            ], $user->id);

            $wsInfo = '';
            if ($user->allWorkspaces()->count() > 1) {
                $wsInfo = "\n📂 Пространство: {$workspace->name}";
            }

            $this->telegramService->sendMessage(
                $chatId,
                "✅ Задача создана во Входящих:\n<b>{$task->title}</b>{$wsInfo}"
            );
        } catch (\Exception $e) {
            Log::error('Telegram create task error: ' . $e->getMessage());
            $this->telegramService->sendMessage(
                $chatId,
                "Не удалось создать задачу. Попробуйте позже."
            );
        }

        return response()->json(['ok' => true]);
    }
}
