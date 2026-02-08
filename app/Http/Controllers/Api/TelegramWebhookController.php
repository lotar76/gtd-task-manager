<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramSetting;
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
        // Находим workspace по webhook_secret
        $setting = TelegramSetting::where('webhook_secret', $secret)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return response()->json(['ok' => false], 404);
        }

        $update = $request->all();
        $message = $update['message'] ?? null;

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $message['chat']['id'];
        $text = trim($message['text']);
        $botToken = $setting->bot_token;

        // Обработка команды /start
        if (str_starts_with($text, '/start')) {
            return $this->handleStart($setting, $chatId, $text, $botToken);
        }

        // Определяем пользователя по chat_id
        $subscription = TelegramSubscription::where('workspace_id', $setting->workspace_id)
            ->where('chat_id', $chatId)
            ->where('is_active', true)
            ->first();

        if (!$subscription) {
            $this->telegramService->sendMessage(
                $botToken,
                $chatId,
                "Вы не подключены к этому боту.\nИспользуйте ссылку из приложения для подключения."
            );
            return response()->json(['ok' => true]);
        }

        // Обработка команд
        if ($text === '/today') {
            return $this->handleToday($setting, $subscription, $botToken, $chatId);
        }

        if ($text === '/help') {
            return $this->handleHelp($botToken, $chatId);
        }

        // Любой текст — создать задачу
        return $this->handleCreateTask($setting, $subscription, $botToken, $chatId, $text);
    }

    private function handleStart(TelegramSetting $setting, string $chatId, string $text, string $botToken): JsonResponse
    {
        $parts = explode(' ', $text, 2);
        $linkToken = $parts[1] ?? null;

        if (!$linkToken) {
            $this->telegramService->sendMessage(
                $botToken,
                $chatId,
                "Для подключения используйте ссылку из приложения GTD Task Manager.\n\nОткройте настройки Telegram в приложении и нажмите \"Подключить\"."
            );
            return response()->json(['ok' => true]);
        }

        // Ищем подписку по токену
        $subscription = TelegramSubscription::where('link_token', $linkToken)
            ->where('workspace_id', $setting->workspace_id)
            ->first();

        if (!$subscription) {
            $this->telegramService->sendMessage(
                $botToken,
                $chatId,
                "Ссылка недействительна или устарела.\nПопробуйте создать новую ссылку в приложении."
            );
            return response()->json(['ok' => true]);
        }

        // Привязываем chat_id
        $subscription->update([
            'chat_id' => $chatId,
            'is_active' => true,
        ]);

        $workspace = $setting->workspace;
        $user = $subscription->user;

        $this->telegramService->sendMessage(
            $botToken,
            $chatId,
            "Привет, {$user->name}! Вы подключены к пространству <b>{$workspace->name}</b>.\n\n"
            . "Что я умею:\n"
            . "- Отправьте текст — создам задачу во Входящих\n"
            . "- /today — список задач на сегодня\n"
            . "- /help — справка\n\n"
            . "Настройте уведомления в приложении."
        );

        return response()->json(['ok' => true]);
    }

    private function handleToday(TelegramSetting $setting, TelegramSubscription $subscription, string $botToken, string $chatId): JsonResponse
    {
        $workspace = $setting->workspace;
        $tasks = $workspace->tasks()
            ->where('status', 'today')
            ->where(function ($q) use ($subscription) {
                $q->where('assigned_to', $subscription->user_id)
                  ->orWhere('created_by', $subscription->user_id);
            })
            ->orderBy('priority', 'desc')
            ->get();

        if ($tasks->isEmpty()) {
            $this->telegramService->sendMessage($botToken, $chatId, "На сегодня задач нет.");
            return response()->json(['ok' => true]);
        }

        $text = "<b>Задачи на сегодня:</b>\n\n";
        foreach ($tasks as $i => $task) {
            $status = $task->status === 'completed' ? ' ' : ' ';
            $priority = $task->priority ? " [{$task->priority}]" : '';
            $text .= ($i + 1) . ". {$status}{$task->title}{$priority}\n";
        }

        $this->telegramService->sendMessage($botToken, $chatId, $text);
        return response()->json(['ok' => true]);
    }

    private function handleHelp(string $botToken, string $chatId): JsonResponse
    {
        $text = "<b>GTD Task Manager Bot</b>\n\n"
            . "Команды:\n"
            . "/today — задачи на сегодня\n"
            . "/help — эта справка\n\n"
            . "Отправьте любой текст — и я создам задачу во Входящих.\n\n"
            . "Настройте уведомления в приложении:\n"
            . "- Утренний дайджест\n"
            . "- Напоминания о задачах\n"
            . "- Просроченные задачи";

        $this->telegramService->sendMessage($botToken, $chatId, $text);
        return response()->json(['ok' => true]);
    }

    private function handleCreateTask(TelegramSetting $setting, TelegramSubscription $subscription, string $botToken, string $chatId, string $text): JsonResponse
    {
        try {
            $workspace = $setting->workspace;
            $task = $this->taskService->createTask($workspace, [
                'title' => $text,
                'status' => 'inbox',
            ], $subscription->user_id);

            $this->telegramService->sendMessage(
                $botToken,
                $chatId,
                "Задача создана во Входящих:\n<b>{$task->title}</b>"
            );
        } catch (\Exception $e) {
            Log::error('Telegram create task error: ' . $e->getMessage());
            $this->telegramService->sendMessage(
                $botToken,
                $chatId,
                "Не удалось создать задачу. Попробуйте позже."
            );
        }

        return response()->json(['ok' => true]);
    }
}
