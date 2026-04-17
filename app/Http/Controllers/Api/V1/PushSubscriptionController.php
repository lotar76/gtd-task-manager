<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Notifications\TaskReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * VAPID public key для клиента.
     */
    public function vapidPublicKey(): JsonResponse
    {
        return response()->json([
            'public_key' => config('webpush.vapid.public_key'),
        ]);
    }

    /**
     * Сохранить push-подписку браузера.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
        );

        return response()->json(['success' => true]);
    }

    /**
     * Отправить тестовое push-уведомление.
     */
    public function test(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $task = $user->createdTasks()->latest()->first();

        if (!$task) {
            return response()->json(['error' => 'Нет задач для теста'], 404);
        }

        $user->notify(new TaskReminder($task));

        return response()->json(['success' => true, 'task' => $task->title]);
    }

    /**
     * Удалить push-подписку (отписка).
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|url',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->deletePushSubscription($validated['endpoint']);

        return response()->json(['success' => true]);
    }
}
