<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
        ]);
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
