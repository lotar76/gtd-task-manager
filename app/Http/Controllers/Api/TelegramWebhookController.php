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

        $this->telegramService->sendMessage(
            $chatId,
            "Привет, {$user->name}! 👋\n"
            . "Вы подключены к GTD Task Manager.\n\n"
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
        $workspaceIds = $user->allWorkspaces()->pluck('id');

        $allTasks = Task::whereIn('workspace_id', $workspaceIds)
            ->with(['project', 'context'])
            ->where('status', 'today')
            ->whereNull('completed_at')
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->orderBy('estimated_time', 'asc')
            ->orderBy('priority', 'desc')
            ->get();

        if ($allTasks->isEmpty()) {
            $this->telegramService->sendMessage($chatId, "На сегодня задач нет 🎉");
            return response()->json(['ok' => true]);
        }

        $text = "<b>📋 Задачи на сегодня ({$allTasks->count()}):</b>\n\n";

        $keyboard = [];

        foreach ($allTasks as $i => $task) {
            $num = $i + 1;
            $line = $this->telegramService->formatTaskLine($task);
            $text .= "{$num}. {$line}\n\n";
            $keyboard[] = [['text' => "✅ {$num}. Отметить как выполнено", 'callback_data' => "done:{$task->id}"]];
        }

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
            . "Отправьте любой текст — и я создам задачу.\n\n"
            . "<b>Уведомления:</b>\n"
            . "Настройте в приложении → Настройки → Telegram:\n"
            . "☀️ Утренний дайджест\n"
            . "🔔 Напоминания о задачах\n"
            . "⚠️ Просроченные задачи";

        $this->telegramService->sendMessage($chatId, $text);
        return response()->json(['ok' => true]);
    }

    private function handleCreateTask(TelegramSubscription $subscription, string $chatId, string $text): JsonResponse
    {
        try {
            $user = $subscription->user;

            $task = $this->taskService->createTask($user, [
                'title' => $text,
                'status' => 'inbox',
            ], $user->id);

            $this->telegramService->sendMessageWithKeyboard(
                $chatId,
                "✅ Задача создана во Входящих\n\n"
                . "<b>{$task->title}</b>",
                [[['text' => 'Отметить как выполнено', 'callback_data' => "done:{$task->id}"]]]
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
            ->with(['project'])
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->first();

        if (!$task) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Задача не найдена');
            return response()->json(['ok' => true]);
        }

        if ($task->completed_at) {
            $this->telegramService->answerCallbackQuery($callbackId, 'Уже выполнена');
            return response()->json(['ok' => true]);
        }

        $this->taskService->completeTask($task, $user->id);

        $this->telegramService->answerCallbackQuery($callbackId, '✅ Задача закрыта!');

        $this->telegramService->editMessageText(
            $chatId,
            $messageId,
            "✅ <s>{$task->title}</s>\n\nЗадача выполнена!"
        );

        return response()->json(['ok' => true]);
    }
}
