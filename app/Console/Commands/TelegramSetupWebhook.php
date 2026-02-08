<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetupWebhook extends Command
{
    protected $signature = 'telegram:setup-webhook';
    protected $description = 'Зарегистрировать webhook для Telegram бота';

    public function handle(TelegramService $telegramService): int
    {
        $botToken = config('services.telegram.bot_token');
        $webhookSecret = config('services.telegram.webhook_secret');

        if (!$botToken) {
            $this->error('TELEGRAM_BOT_TOKEN не задан в .env');
            return Command::FAILURE;
        }

        if (!$webhookSecret) {
            $this->error('TELEGRAM_WEBHOOK_SECRET не задан в .env');
            return Command::FAILURE;
        }

        // Проверяем валидность токена
        $botInfo = $telegramService->getMe();
        if (!$botInfo) {
            $this->error('Невалидный токен бота. Проверьте TELEGRAM_BOT_TOKEN.');
            return Command::FAILURE;
        }

        $this->info("Бот: @{$botInfo['username']}");

        // Формируем URL webhook
        $appUrl = config('app.url');
        $webhookUrl = "{$appUrl}/api/v1/telegram/webhook/{$webhookSecret}";

        $this->info("Webhook URL: {$webhookUrl}");

        // Устанавливаем webhook
        if ($telegramService->setWebhook($webhookUrl)) {
            $this->info('Webhook успешно установлен!');

            // Обновляем bot_username в .env если не задан
            $configUsername = config('services.telegram.bot_username');
            if (!$configUsername && isset($botInfo['username'])) {
                $this->warn("Не забудьте добавить TELEGRAM_BOT_USERNAME={$botInfo['username']} в .env");
            }

            return Command::SUCCESS;
        }

        $this->error('Не удалось установить webhook.');
        return Command::FAILURE;
    }
}
