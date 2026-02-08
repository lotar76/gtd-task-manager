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

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
        ]);
    }

    /**
     * Format a single task as a detailed block (for reminders, creation confirmation, etc.)
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
            $lines[] = $desc;
        }

        $meta = [];

        if ($task->due_date) {
            $meta[] = '📅 ' . Carbon::parse($task->due_date)->format('d.m.Y');
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

        if ($task->context) {
            $meta[] = '📍 ' . $task->context->name;
        }

        if (!empty($meta)) {
            $lines[] = implode('  ', $meta);
        }

        return implode("\n", $lines);
    }

    /**
     * Format a task as a single line for lists (/today, digest, etc.)
     */
    public function formatTaskLine(Task $task, bool $showDate = false): string
    {
        $task->loadMissing(['project']);

        $parts = [$task->title];

        if ($task->estimated_time) {
            $time = substr($task->estimated_time, 0, 5);
            if ($task->end_time) {
                $time .= '–' . substr($task->end_time, 0, 5);
            }
            $parts[] = '🕐' . $time;
        }

        if ($showDate && $task->due_date) {
            $parts[] = '📅' . Carbon::parse($task->due_date)->format('d.m');
        }

        if ($task->priority) {
            $icons = ['urgent' => '🔴', 'high' => '🟠', 'medium' => '🟡', 'low' => '🔵'];
            $parts[] = $icons[$task->priority] ?? '';
        }

        if ($task->project) {
            $parts[] = '📁' . $task->project->name;
        }

        return implode('  ', $parts);
    }

    public function sendMessage(string $botToken, string $chatId, string $text, string $parseMode = 'HTML'): bool
    {
        try {
            $response = $this->client->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => $parseMode,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ?? false;
        } catch (GuzzleException $e) {
            Log::error('Telegram sendMessage error: ' . $e->getMessage());
            return false;
        }
    }

    public function setWebhook(string $botToken, string $webhookUrl): bool
    {
        try {
            $response = $this->client->post("https://api.telegram.org/bot{$botToken}/setWebhook", [
                'json' => [
                    'url' => $webhookUrl,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ?? false;
        } catch (GuzzleException $e) {
            Log::error('Telegram setWebhook error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteWebhook(string $botToken): bool
    {
        try {
            $response = $this->client->post("https://api.telegram.org/bot{$botToken}/deleteWebhook");

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['ok'] ?? false;
        } catch (GuzzleException $e) {
            Log::error('Telegram deleteWebhook error: ' . $e->getMessage());
            return false;
        }
    }

    public function getMe(string $botToken): ?array
    {
        try {
            $response = $this->client->get("https://api.telegram.org/bot{$botToken}/getMe");

            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['ok'] ?? false) {
                return $data['result'];
            }
            return null;
        } catch (GuzzleException $e) {
            Log::error('Telegram getMe error: ' . $e->getMessage());
            return null;
        }
    }
}
