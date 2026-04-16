<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use App\Models\TelegramSubscription;
use App\Models\User;
use App\Models\Workspace;
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

        // Описание (до 100 символов)
        if ($task->description) {
            $desc = mb_substr($task->description, 0, 100);
            if (mb_strlen($task->description) > 100) {
                $desc .= '...';
            }
            $line .= "\n     " . $desc;
        }

        $meta = [];

        if ($task->due_date) {
            $meta[] = '📅 ' . Carbon::parse($task->due_date)->format('d.m');
        }

        if ($task->estimated_time) {
            $time = substr($task->estimated_time, 0, 5);
            if ($task->end_time) {
                $time .= '–' . substr($task->end_time, 0, 5);
            }
            $meta[] = '🕐 ' . $time;
        }

        if ($task->priority) {
            $meta[] = self::PRIORITY_LABELS[$task->priority] ?? $task->priority;
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
     * Уведомить участников workspace о событии с задачей.
     * Отправляет сообщение всем активным Telegram-подписчикам workspace, кроме исполнителя.
     */
    /**
     * Уведомление участников задачи: создатель + исполнители + наблюдатели.
     * Исключается actor (тот, кто совершил действие).
     * $action: created | updated | completed | assigned | commented
     * $extra: дополнительный текст (например, тело комментария)
     */
    public function notifyTaskParticipants(Task $task, User $actor, string $action, ?string $extra = null): void
    {
        $task->loadMissing(['assignees.user:id', 'watchers.user:id']);

        $userIds = [];
        if ($task->created_by) {
            $userIds[] = $task->created_by;
        }
        foreach ($task->assignees as $c) {
            if ($c->contact_user_id) {
                $userIds[] = $c->contact_user_id;
            }
        }
        foreach ($task->watchers as $c) {
            if ($c->contact_user_id) {
                $userIds[] = $c->contact_user_id;
            }
        }
        $userIds = array_values(array_unique(array_filter($userIds, fn ($id) => $id !== $actor->id)));

        if (empty($userIds)) {
            return;
        }

        $subscriptions = TelegramSubscription::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->whereNotNull('chat_id')
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $actionHeaders = [
            'created' => "📝 <b>{$actor->name}</b> создал задачу",
            'updated' => "✏️ <b>{$actor->name}</b> обновил задачу",
            'completed' => "✅ <b>{$actor->name}</b> выполнил задачу",
            'assigned' => "👤 <b>{$actor->name}</b> назначил задачу",
            'commented' => "💬 <b>{$actor->name}</b> прокомментировал задачу",
        ];
        $header = $actionHeaders[$action] ?? "📌 <b>{$actor->name}</b> обновил задачу";
        $message = "{$header}\n\n" . $this->formatTaskLine($task);
        if ($extra) {
            $message .= "\n\n<i>" . e($extra) . "</i>";
        }

        foreach ($subscriptions as $sub) {
            $this->sendMessage($sub->chat_id, $message);
        }
    }

    public function notifyWorkspaceMembers(Task $task, User $actor, string $action): void
    {
        $workspace = $task->workspace;
        if (!$workspace) {
            return;
        }

        // Собираем ID всех участников workspace (members + owner)
        $memberIds = $workspace->members()->pluck('users.id')->toArray();
        if (!in_array($workspace->owner_id, $memberIds)) {
            $memberIds[] = $workspace->owner_id;
        }

        // Убираем того, кто совершил действие
        $memberIds = array_filter($memberIds, fn($id) => $id !== $actor->id);

        if (empty($memberIds)) {
            return;
        }

        // Находим активные Telegram-подписки этих пользователей
        $subscriptions = TelegramSubscription::whereIn('user_id', $memberIds)
            ->where('is_active', true)
            ->whereNotNull('chat_id')
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        // Формируем сообщение
        $actorName = $actor->name;
        $taskTitle = $task->title;
        $wsName = $workspace->name;

        $actionTexts = [
            'created' => "📝 <b>{$actorName}</b> создал задачу",
            'completed' => "✅ <b>{$actorName}</b> выполнил задачу",
            'assigned' => "👤 <b>{$actorName}</b> назначил задачу",
        ];

        $header = $actionTexts[$action] ?? "📌 <b>{$actorName}</b> обновил задачу";
        $message = "{$header}\n\n";
        $message .= $this->formatTaskLine($task);
        $message .= "\n\n🏢 {$wsName}";

        // Для назначения — уведомляем только назначенного
        if ($action === 'assigned' && $task->assigned_to) {
            $subscriptions = $subscriptions->where('user_id', $task->assigned_to);
        }

        foreach ($subscriptions as $sub) {
            $this->sendMessage($sub->chat_id, $message);
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
