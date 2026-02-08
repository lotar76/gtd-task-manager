<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private Client $client;

    private const PRIORITY_LABELS = [
        'urgent' => '🔴 Срочный',
        'high' => '🟠 Высокий',
        'medium' => '🟡 Средний',
        'low' => '🔵 Низкий',
    ];

    private const PRIORITY_ICONS = [
        'urgent' => '🔴',
        'high' => '🟠',
        'medium' => '🟡',
        'low' => '🔵',
    ];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
        ]);
    }

    private function getBotToken(): ?string
    {
        return config('services.telegram.bot_token');
    }

    /**
     * Форматирует задачу в читаемое многострочное сообщение.
     */
    public function formatTask(Task $task): string
    {
        $task->loadMissing(['project', 'context']);

        $lines = [];
        $lines[] = "<b>{$task->title}</b>";

        if ($task->description) {
            $desc = mb_substr($task->description, 0, 200);
            if (mb_strlen($task->description) > 200) {
                $desc .= '...';
            }
            $lines[] = '';
            $lines[] = $desc;
        }

        $lines[] = '';

        if ($task->due_date) {
            $lines[] = '📅 ' . Carbon::parse($task->due_date)->format('d.m.Y');
        }

        if ($task->estimated_time) {
            $time = substr($task->estimated_time, 0, 5);
            if ($task->end_time) {
                $time .= ' – ' . substr($task->end_time, 0, 5);
            }
            $lines[] = '🕐 ' . $time;
        }

        if ($task->priority) {
            $lines[] = self::PRIORITY_LABELS[$task->priority] ?? $task->priority;
        }

        if ($task->project) {
            $lines[] = '📁 ' . $task->project->name;
        }

        if ($task->context) {
            $lines[] = '📍 ' . $task->context->name;
        }

        return implode("\n", $lines);
    }

    /**
     * Форматирует задачу для списка (компактно, но читаемо — каждая на 2 строки).
     */
    public function formatTaskLine(Task $task, bool $showDate = false): string
    {
        $task->loadMissing(['project']);

        $line = "<b>{$task->title}</b>";
        $meta = [];

        if ($task->estimated_time) {
            $time = substr($task->estimated_time, 0, 5);
            if ($task->end_time) {
                $time .= '–' . substr($task->end_time, 0, 5);
            }
            $meta[] = '🕐 ' . $time;
        }

        if ($showDate && $task->due_date) {
            $meta[] = '📅 ' . Carbon::parse($task->due_date)->format('d.m');
        }

        if ($task->priority) {
            $meta[] = self::PRIORITY_ICONS[$task->priority] ?? '';
        }

        if ($task->project) {
            $meta[] = '📁 ' . $task->project->name;
        }

        if (!empty($meta)) {
            $line .= "\n     " . implode('  ', $meta);
        }

        return $line;
    }

    /**
     * Отправить сообщение.
     */
    public function sendMessage(string $chatId, string $text, string $parseMode = 'HTML'): bool
    {
        return $this->apiCall('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ]);
    }

    /**
     * Отправить сообщение с inline-кнопками.
     */
    public function sendMessageWithKeyboard(string $chatId, string $text, array $keyboard, string $parseMode = 'HTML'): bool
    {
        return $this->apiCall('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    /**
     * Ответить на callback query (убирает "часики" на кнопке).
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = ''): bool
    {
        return $this->apiCall('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]);
    }

    /**
     * Редактировать текст существующего сообщения.
     */
    public function editMessageText(string $chatId, int $messageId, string $text, string $parseMode = 'HTML', ?array $keyboard = null): bool
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];

        if ($keyboard !== null) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => $keyboard,
            ]);
        } else {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => [],
            ]);
        }

        return $this->apiCall('editMessageText', $params);
    }

    public function setWebhook(string $webhookUrl): bool
    {
        return $this->apiCall('setWebhook', ['url' => $webhookUrl]);
    }

    public function deleteWebhook(): bool
    {
        return $this->apiCall('deleteWebhook');
    }

    public function getMe(): ?array
    {
        $botToken = $this->getBotToken();
        if (!$botToken) {
            return null;
        }

        try {
            $response = $this->client->get("https://api.telegram.org/bot{$botToken}/getMe");
            $data = json_decode($response->getBody()->getContents(), true);
            return ($data['ok'] ?? false) ? $data['result'] : null;
        } catch (GuzzleException $e) {
            Log::error('Telegram getMe error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Общий метод для вызовов Telegram Bot API.
     */
    private function apiCall(string $method, array $params = []): bool
    {
        $botToken = $this->getBotToken();
        if (!$botToken) {
            Log::error('Telegram bot token not configured');
            return false;
        }

        try {
            $response = $this->client->post("https://api.telegram.org/bot{$botToken}/{$method}", [
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ?? false;
        } catch (GuzzleException $e) {
            Log::error("Telegram {$method} error: " . $e->getMessage());
            return false;
        }
    }
}
