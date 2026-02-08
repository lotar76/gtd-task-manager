<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
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

        // Обработка callback query (нажатие inline-кнопки)
        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query']);
        }

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

    // ─── Команды ───────────────────────────────────────────────

    private function handleStart(string $chatId, string $text): JsonResponse
    {
        $parts = explode(' ', $text, 2);
        $linkToken = $parts[1] ?? null;

        if (!$linkToken) {
            $this->telegramService->sendMessage(
                $chatId,
                "Для подключения используйте ссылку из приложения GTD Task Manager.\n\n"
                . "Откройте настройки Telegram в приложении и нажмите «Подключить»."
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
            "Привет, {$user->name}! 👋\n"
            . "Вы подключены к GTD Task Manager.\n\n"
            . "📂 Ваши пространства:\n"
            . "<b>{$wsNames}</b>\n\n"
            . "Что я умею:\n"
            . "💬 Отправьте текст — создам задачу\n"
            . "📋 /today — задачи на сегодня\n"
            . "❓ /help — справка\n\n"
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
                ->each(fn ($task) => $task->_workspace_name = $workspace->name);

            $allTasks = $allTasks->merge($tasks);
        }

        $allTasks = $allTasks->sortBy([
            ['estimated_time', 'asc'],
            ['priority', 'desc'],
        ])->values();

        if ($allTasks->isEmpty()) {
            $this->telegramService->sendMessage($chatId, "На сегодня задач нет 🎉");
            return response()->json(['ok' => true]);
        }

        $showWorkspaceName = $workspaces->count() > 1;

        $text = "<b>📋 Задачи на сегодня ({$allTasks->count()}):</b>\n\n";

        $buttons = [];

        foreach ($allTasks as $i => $task) {
            $num = $i + 1;
            $line = $this->telegramService->formatTaskLine($task);

            if ($showWorkspaceName) {
                $line .= "\n     📂 {$task->_workspace_name}";
            }

            $text .= "{$num}. {$line}\n\n";

            $buttons[] = [
                'text' => "✅ {$num}",
                'callback_data' => "done:{$task->id}",
            ];
        }

        // Разбиваем кнопки по 5 в ряд
        $keyboard = array_chunk($buttons, 5);

        $this->telegramService->sendMessageWithKeyboard($chatId, $text, $keyboard);
        return response()->json(['ok' => true]);
    }

    private function handleHelp(string $chatId): JsonResponse
    {
        $text = "<b>GTD Task Manager Bot</b>\n\n"
            . "<b>Команды:</b>\n"
            . "📋 /today — задачи на сегодня\n"
            . "❓ /help — эта справка\n\n"
            . "<b>Создание задач:</b>\n"
            . "Отправьте любой текст — и я создам задачу.\n"
            . "Если у вас несколько пространств, предложу выбрать.\n\n"
            . "<b>Уведомления:</b>\n"
            . "Настройте в приложении → Настройки → Telegram:\n"
            . "☀️ Утренний дайджест\n"
            . "🔔 Напоминания о задачах\n"
            . "⚠️ Просроченные задачи";

        $this->telegramService->sendMessage($chatId, $text);
        return response()->json(['ok' => true]);
    }

    // ─── Создание задачи ───────────────────────────────────────

    private function handleCreateTask(TelegramSubscription $subscription, string $chatId, string $text): JsonResponse
    {
        try {
            $user = $subscription->user;
            $workspaces = $user->allWorkspaces();

            if ($workspaces->isEmpty()) {
                $this->telegramService->sendMessage(
                    $chatId,
                    "У вас нет ни одного пространства.\nСоздайте пространство в приложении."
                );
                return response()->json(['ok' => true]);
            }

            // Одно пространство — создаём сразу
            if ($workspaces->count() === 1) {
                $workspace = $workspaces->first();
                $task = $this->taskService->createTask($workspace, [
                    'title' => $text,
                    'status' => 'inbox',
                ], $user->id);

                $this->telegramService->sendMessageWithKeyboard(
                    $chatId,
                    "✅ Задача создана во Входящих\n\n"
                    . "<b>{$task->title}</b>\n"
                    . "📂 {$workspace->name}",
                    [[['text' => 'Отметить как выполнено', 'callback_data' => "done:{$task->id}"]]]
                );

                return response()->json(['ok' => true]);
            }

            // Несколько пространств — показываем кнопки выбора
            $subscription->update(['pending_task_text' => $text]);

            $keyboard = [];
            foreach ($workspaces as $ws) {
                $keyboard[] = [['text' => "📂 {$ws->name}", 'callback_data' => "ws:{$ws->id}"]];
            }

            $this->telegramService->sendMessageWithKeyboard(
                $chatId,
                "📝 Создать задачу:\n<b>{$text}</b>\n\nВыберите пространство:",
                $keyboard
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

    // ─── Callback Query (нажатие кнопки) ───────────────────────

    private function handleCallbackQuery(array $callbackQuery): JsonResponse
    {
        $callbackId = $callbackQuery['id'];
        $data = $callbackQuery['data'] ?? '';
        $chatId = (string) ($callbackQuery['message']['chat']['id'] ?? '');
        $messageId = (int) ($callbackQuery['message']['message_id'] ?? 0);

        if (!$chatId || !$messageId) {
            $this->telegramService->answerCallbackQuery($callbackId);
            return response()->json(['ok' => true]);
        }

        $subscription = TelegramSubscription::where('chat_id', $chatId)
            ->where('is_active', true)
            ->with('user')
            ->first();

        if (!$subscription) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Подписка не найдена');
            return response()->json(['ok' => true]);
        }

        // done:{task_id} — закрыть задачу
        if (str_starts_with($data, 'done:')) {
            $taskId = (int) substr($data, 5);
            return $this->handleCompleteTask($subscription, $callbackId, $chatId, $messageId, $taskId);
        }

        // ws:{workspace_id} — выбрать пространство для создания задачи
        if (str_starts_with($data, 'ws:')) {
            $workspaceId = (int) substr($data, 3);
            return $this->handleSelectWorkspace($subscription, $callbackId, $chatId, $messageId, $workspaceId);
        }

        $this->telegramService->answerCallbackQuery($callbackId);
        return response()->json(['ok' => true]);
    }

    private function handleCompleteTask(
        TelegramSubscription $subscription,
        string $callbackId,
        string $chatId,
        int $messageId,
        int $taskId
    ): JsonResponse {
        $user = $subscription->user;

        $task = Task::where('id', $taskId)
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->first();

        if (!$task) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Задача не найдена');
            return response()->json(['ok' => true]);
        }

        if ($task->status === 'completed') {
            $this->telegramService->answerCallbackQuery($callbackId, 'Уже выполнена');
            return response()->json(['ok' => true]);
        }

        $this->taskService->completeTask($task);

        $this->telegramService->answerCallbackQuery($callbackId, '✅ Задача закрыта!');

        // Обновляем сообщение — зачёркиваем задачу
        $this->telegramService->editMessageText(
            $chatId,
            $messageId,
            "✅ <s>{$task->title}</s>\n\nЗадача выполнена!"
        );

        return response()->json(['ok' => true]);
    }

    private function handleSelectWorkspace(
        TelegramSubscription $subscription,
        string $callbackId,
        string $chatId,
        int $messageId,
        int $workspaceId
    ): JsonResponse {
        $user = $subscription->user;
        $pendingText = $subscription->pending_task_text;

        if (!$pendingText) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Нет текста задачи');
            return response()->json(['ok' => true]);
        }

        $workspace = $user->allWorkspaces()->firstWhere('id', $workspaceId);

        if (!$workspace) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Пространство не найдено');
            return response()->json(['ok' => true]);
        }

        try {
            $task = $this->taskService->createTask($workspace, [
                'title' => $pendingText,
                'status' => 'inbox',
            ], $user->id);

            $subscription->update(['pending_task_text' => null]);

            $this->telegramService->answerCallbackQuery($callbackId, '✅ Задача создана!');

            // Обновляем сообщение — показываем подтверждение с кнопкой «Закрыть»
            $this->telegramService->editMessageText(
                $chatId,
                $messageId,
                "✅ Задача создана во Входящих\n\n"
                . "<b>{$task->title}</b>\n"
                . "📂 {$workspace->name}",
                'HTML',
                [[['text' => 'Отметить как выполнено', 'callback_data' => "done:{$task->id}"]]]
            );
        } catch (\Exception $e) {
            Log::error('Telegram select workspace error: ' . $e->getMessage());
            $this->telegramService->answerCallbackQuery($callbackId, 'Ошибка создания задачи');
        }

        return response()->json(['ok' => true]);
    }
}
