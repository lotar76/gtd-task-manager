<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\TelegramSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramSubscriptionController extends Controller
{
    public function show(): JsonResponse
    {
        $botUsername = config('services.telegram.bot_username');

        if (!$botUsername || !config('services.telegram.bot_token')) {
            return ApiResponse::success([
                'bot_configured' => false,
            ]);
        }

        $subscription = TelegramSubscription::where('user_id', auth()->id())->first();

        if (!$subscription) {
            return ApiResponse::success([
                'bot_configured' => true,
                'bot_username' => $botUsername,
                'subscribed' => false,
            ]);
        }

        return ApiResponse::success([
            'bot_configured' => true,
            'bot_username' => $botUsername,
            'subscribed' => true,
            'is_active' => $subscription->is_active,
            'link_token' => $subscription->link_token,
            'connect_url' => "https://t.me/{$botUsername}?start={$subscription->link_token}",
            'default_workspace_id' => $subscription->default_workspace_id,
            'morning_digest_time' => $subscription->morning_digest_time,
            'reminder_minutes_before' => $subscription->reminder_minutes_before,
            'notify_overdue' => $subscription->notify_overdue,
            'notify_morning_digest' => $subscription->notify_morning_digest,
            'notify_reminders' => $subscription->notify_reminders,
        ]);
    }

    public function store(): JsonResponse
    {
        $botUsername = config('services.telegram.bot_username');

        if (!$botUsername || !config('services.telegram.bot_token')) {
            return ApiResponse::error('Telegram бот не настроен', 400);
        }

        $existing = TelegramSubscription::where('user_id', auth()->id())->first();

        if ($existing) {
            return ApiResponse::success([
                'connect_url' => "https://t.me/{$botUsername}?start={$existing->link_token}",
                'is_active' => $existing->is_active,
            ], 'Подписка уже существует');
        }

        $linkToken = Str::random(64);

        $user = auth()->user();
        $defaultWorkspace = $user->allWorkspaces()->first();

        TelegramSubscription::create([
            'user_id' => $user->id,
            'default_workspace_id' => $defaultWorkspace?->id,
            'link_token' => $linkToken,
        ]);

        return ApiResponse::success([
            'connect_url' => "https://t.me/{$botUsername}?start={$linkToken}",
            'is_active' => false,
        ], 'Подписка создана. Перейдите по ссылке и нажмите Start в Telegram.');
    }

    public function update(Request $request): JsonResponse
    {
        $subscription = TelegramSubscription::where('user_id', auth()->id())->first();

        if (!$subscription) {
            return ApiResponse::error('Подписка не найдена', 404);
        }

        $validated = $request->validate([
            'default_workspace_id' => 'sometimes|nullable|integer|exists:workspaces,id',
            'morning_digest_time' => 'sometimes|string|regex:/^\d{2}:\d{2}$/',
            'reminder_minutes_before' => 'sometimes|integer|min:5|max:120',
            'notify_overdue' => 'sometimes|boolean',
            'notify_morning_digest' => 'sometimes|boolean',
            'notify_reminders' => 'sometimes|boolean',
        ]);

        $subscription->update($validated);

        return ApiResponse::success([
            'default_workspace_id' => $subscription->default_workspace_id,
            'morning_digest_time' => $subscription->morning_digest_time,
            'reminder_minutes_before' => $subscription->reminder_minutes_before,
            'notify_overdue' => $subscription->notify_overdue,
            'notify_morning_digest' => $subscription->notify_morning_digest,
            'notify_reminders' => $subscription->notify_reminders,
        ], 'Настройки обновлены');
    }

    public function destroy(): JsonResponse
    {
        $subscription = TelegramSubscription::where('user_id', auth()->id())->first();

        if (!$subscription) {
            return ApiResponse::error('Подписка не найдена', 404);
        }

        $subscription->delete();

        return ApiResponse::success(null, 'Подписка на Telegram отключена');
    }
}
