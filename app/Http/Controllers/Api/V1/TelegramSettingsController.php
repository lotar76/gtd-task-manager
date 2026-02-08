<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\TelegramSetting;
use App\Models\Workspace;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramSettingsController extends Controller
{
    public function __construct(
        private TelegramService $telegramService
    ) {}

    public function show(Workspace $workspace): JsonResponse
    {
        if ($workspace->owner_id !== auth()->id()) {
            return ApiResponse::error('Доступно только владельцу пространства', 403);
        }

        $setting = $workspace->telegramSetting;

        if (!$setting) {
            return ApiResponse::success([
                'is_configured' => false,
            ]);
        }

        return ApiResponse::success([
            'is_configured' => true,
            'has_bot_token' => true,
            'bot_username' => $setting->bot_username,
            'is_active' => $setting->is_active,
            'subscribers_count' => $workspace->telegramSubscriptions()->where('is_active', true)->count(),
            'total_subscriptions' => $workspace->telegramSubscriptions()->count(),
        ]);
    }

    public function store(Request $request, Workspace $workspace): JsonResponse
    {
        if ($workspace->owner_id !== auth()->id()) {
            return ApiResponse::error('Доступно только владельцу пространства', 403);
        }

        $request->validate([
            'bot_token' => 'required|string',
        ]);

        $botToken = $request->input('bot_token');

        // Валидируем токен через Telegram API
        $botInfo = $this->telegramService->getMe($botToken);
        if (!$botInfo) {
            return ApiResponse::error('Невалидный токен бота. Проверьте токен и попробуйте снова.', 422);
        }

        $webhookSecret = Str::random(64);
        $webhookUrl = url("/api/v1/telegram/webhook/{$webhookSecret}");

        // Устанавливаем webhook
        if (!$this->telegramService->setWebhook($botToken, $webhookUrl)) {
            return ApiResponse::error('Не удалось установить webhook. Попробуйте позже.', 500);
        }

        // Создаём или обновляем настройки
        $setting = TelegramSetting::updateOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'bot_token' => $botToken,
                'bot_username' => $botInfo['username'] ?? null,
                'webhook_secret' => $webhookSecret,
                'is_active' => true,
            ]
        );

        return ApiResponse::success([
            'is_configured' => true,
            'bot_username' => $setting->bot_username,
            'is_active' => true,
        ], 'Бот успешно подключен');
    }

    public function destroy(Workspace $workspace): JsonResponse
    {
        if ($workspace->owner_id !== auth()->id()) {
            return ApiResponse::error('Доступно только владельцу пространства', 403);
        }

        $setting = $workspace->telegramSetting;

        if (!$setting) {
            return ApiResponse::error('Telegram не настроен', 404);
        }

        // Удаляем webhook
        $this->telegramService->deleteWebhook($setting->bot_token);

        // Удаляем все подписки
        $workspace->telegramSubscriptions()->delete();

        // Удаляем настройки
        $setting->delete();

        return ApiResponse::success(null, 'Telegram отключен');
    }
}
