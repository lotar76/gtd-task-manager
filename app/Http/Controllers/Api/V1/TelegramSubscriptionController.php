<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramSubscriptionController extends Controller
{
    public function show(Workspace $workspace): JsonResponse
    {
        if (!$workspace->hasMember(auth()->id())) {
            return ApiResponse::error('Вы не участник этого пространства', 403);
        }

        $setting = $workspace->telegramSetting;
        if (!$setting || !$setting->is_active) {
            return ApiResponse::success([
                'bot_configured' => false,
            ]);
        }

        $subscription = $workspace->telegramSubscriptions()
            ->where('user_id', auth()->id())
            ->first();

        if (!$subscription) {
            return ApiResponse::success([
                'bot_configured' => true,
                'bot_username' => $setting->bot_username,
                'subscribed' => false,
            ]);
        }

        return ApiResponse::success([
            'bot_configured' => true,
            'bot_username' => $setting->bot_username,
            'subscribed' => true,
            'is_active' => $subscription->is_active,
            'link_token' => $subscription->link_token,
            'connect_url' => "https://t.me/{$setting->bot_username}?start={$subscription->link_token}",
            'morning_digest_time' => $subscription->morning_digest_time,
            'reminder_minutes_before' => $subscription->reminder_minutes_before,
            'notify_overdue' => $subscription->notify_overdue,
            'notify_morning_digest' => $subscription->notify_morning_digest,
            'notify_reminders' => $subscription->notify_reminders,
        ]);
    }

    public function store(Workspace $workspace): JsonResponse
    {
        if (!$workspace->hasMember(auth()->id())) {
            return ApiResponse::error('Вы не участник этого пространства', 403);
        }

        $setting = $workspace->telegramSetting;
        if (!$setting || !$setting->is_active) {
            return ApiResponse::error('Telegram бот не настроен для этого пространства', 400);
        }

        // Проверяем, нет ли уже подписки
        $existing = $workspace->telegramSubscriptions()
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            return ApiResponse::success([
                'connect_url' => "https://t.me/{$setting->bot_username}?start={$existing->link_token}",
                'is_active' => $existing->is_active,
            ], 'Подписка уже существует');
        }

        $linkToken = Str::random(64);

        $subscription = $workspace->telegramSubscriptions()->create([
            'user_id' => auth()->id(),
            'link_token' => $linkToken,
        ]);

        return ApiResponse::success([
            'connect_url' => "https://t.me/{$setting->bot_username}?start={$linkToken}",
            'is_active' => false,
        ], 'Подписка создана. Перейдите по ссылке и нажмите Start в Telegram.');
    }

    public function update(Request $request, Workspace $workspace): JsonResponse
    {
        if (!$workspace->hasMember(auth()->id())) {
            return ApiResponse::error('Вы не участник этого пространства', 403);
        }

        $subscription = $workspace->telegramSubscriptions()
            ->where('user_id', auth()->id())
            ->first();

        if (!$subscription) {
            return ApiResponse::error('Подписка не найдена', 404);
        }

        $validated = $request->validate([
            'morning_digest_time' => 'sometimes|string|regex:/^\d{2}:\d{2}$/',
            'reminder_minutes_before' => 'sometimes|integer|min:5|max:120',
            'notify_overdue' => 'sometimes|boolean',
            'notify_morning_digest' => 'sometimes|boolean',
            'notify_reminders' => 'sometimes|boolean',
        ]);

        $subscription->update($validated);

        return ApiResponse::success([
            'morning_digest_time' => $subscription->morning_digest_time,
            'reminder_minutes_before' => $subscription->reminder_minutes_before,
            'notify_overdue' => $subscription->notify_overdue,
            'notify_morning_digest' => $subscription->notify_morning_digest,
            'notify_reminders' => $subscription->notify_reminders,
        ], 'Настройки обновлены');
    }

    public function destroy(Workspace $workspace): JsonResponse
    {
        if (!$workspace->hasMember(auth()->id())) {
            return ApiResponse::error('Вы не участник этого пространства', 403);
        }

        $subscription = $workspace->telegramSubscriptions()
            ->where('user_id', auth()->id())
            ->first();

        if (!$subscription) {
            return ApiResponse::error('Подписка не найдена', 404);
        }

        $subscription->delete();

        return ApiResponse::success(null, 'Подписка на Telegram отключена');
    }
}
