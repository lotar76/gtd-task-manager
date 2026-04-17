<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttachmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ContextController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\LifeSphereController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TelegramSubscriptionController;
use App\Http\Controllers\Api\V1\ChallengeController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use App\Http\Controllers\Api\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Тестовый роут для проверки работы API
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Все работает! API функционирует корректно.',
        'timestamp' => now()->toDateTimeString(),
        'version' => 'v1'
    ]);
});

// API Version 1
Route::prefix('v1')->group(function () {

    // Telegram Webhook (публичный, защищён через secret в URL)
    Route::post('/telegram/webhook/{secret}', [TelegramWebhookController::class, 'handle']);

    // Публичные маршруты (без аутентификации)
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:auth');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth');

    // Защищённые маршруты (требуют аутентификации)
    Route::middleware('auth:sanctum')->group(function () {

        // Аутентификация
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Профиль
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'updatePassword']);

        // === ЗАДАЧИ (без workspace) ===
        Route::get('/tasks', [TaskController::class, 'all']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::post('/tasks/cleanup-empty', [TaskController::class, 'cleanupEmpty']);
        Route::get('/tasks/{task}', [TaskController::class, 'show']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::post('/tasks/{task}/complete', [TaskController::class, 'complete']);
        Route::post('/tasks/{task}/uncomplete', [TaskController::class, 'uncomplete']);
        Route::post('/tasks/{task}/move', [TaskController::class, 'move']);
        Route::post('/tasks/{task}/assign', [TaskController::class, 'assign']);

        // GTD виды задач
        Route::get('/inbox', [TaskController::class, 'inbox']);
        Route::get('/next-actions', [TaskController::class, 'nextActions']);
        Route::get('/waiting', [TaskController::class, 'waiting']);
        Route::get('/someday', [TaskController::class, 'someday']);
        Route::get('/today', [TaskController::class, 'today']);
        Route::get('/tomorrow', [TaskController::class, 'tomorrow']);
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        Route::get('/calendar', [TaskController::class, 'calendar']);
        Route::get('/counts', [TaskController::class, 'counts']);

        // === ПРОЕКТЫ ===
        Route::get('/projects', [ProjectController::class, 'all']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::get('/projects/{project}/tasks', [ProjectController::class, 'tasks']);

        // === ЦЕЛИ ===
        Route::get('/goals', [GoalController::class, 'all']);
        Route::post('/goals', [GoalController::class, 'store']);
        Route::get('/goals/{goal}', [GoalController::class, 'show']);
        Route::put('/goals/{goal}', [GoalController::class, 'update']);
        Route::delete('/goals/{goal}', [GoalController::class, 'destroy']);
        Route::delete('/goals/{goal}/image', [GoalController::class, 'deleteImage']);

        // === СФЕРЫ ЖИЗНИ ===
        Route::get('/life-spheres', [LifeSphereController::class, 'all']);
        Route::post('/life-spheres', [LifeSphereController::class, 'store']);
        Route::get('/life-spheres/{life_sphere}', [LifeSphereController::class, 'show']);
        Route::put('/life-spheres/{life_sphere}', [LifeSphereController::class, 'update']);
        Route::delete('/life-spheres/{life_sphere}', [LifeSphereController::class, 'destroy']);
        Route::post('/life-spheres/seed', [LifeSphereController::class, 'seed']);

        // === КОНТАКТЫ ===
        Route::apiResource('contacts', ContactController::class);

        // === КОНТЕКСТЫ ===
        Route::get('/contexts', [ContextController::class, 'all']);
        Route::post('/contexts', [ContextController::class, 'store']);
        Route::get('/contexts/{context}', [ContextController::class, 'show']);
        Route::put('/contexts/{context}', [ContextController::class, 'update']);
        Route::delete('/contexts/{context}', [ContextController::class, 'destroy']);

        // === ТЕГИ ===
        Route::get('/tags', [TagController::class, 'all']);
        Route::post('/tags', [TagController::class, 'store']);
        Route::get('/tags/{tag}', [TagController::class, 'show']);
        Route::put('/tags/{tag}', [TagController::class, 'update']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);

        // === КОММЕНТАРИИ ===
        Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
        Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

        // === ВЛОЖЕНИЯ ===
        Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'upload']);
        Route::get('/attachments/{attachment}', [AttachmentController::class, 'show']);
        Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);
        Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);

        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
        Route::get('/dashboard/life-mirror', [DashboardController::class, 'getLifeMirror']);
        Route::get('/dashboard/ai-message', [DashboardController::class, 'getAiMessage']);

        // === ЧЕЛЛЕНДЖИ ===
        Route::get('/challenges', [ChallengeController::class, 'index']);
        Route::post('/challenges', [ChallengeController::class, 'store']);
        Route::put('/challenges/{challenge}', [ChallengeController::class, 'update']);
        Route::delete('/challenges/{challenge}', [ChallengeController::class, 'destroy']);
        Route::post('/challenges/{challenge}/toggle', [ChallengeController::class, 'toggle']);

        // === WORKSPACES (legacy, оставляем для обратной совместимости) ===
        Route::apiResource('workspaces', WorkspaceController::class);

        // Telegram — подписка пользователя
        Route::get('telegram/subscription', [TelegramSubscriptionController::class, 'show']);
        Route::post('telegram/subscription', [TelegramSubscriptionController::class, 'store']);
        Route::put('telegram/subscription', [TelegramSubscriptionController::class, 'update']);
        Route::delete('telegram/subscription', [TelegramSubscriptionController::class, 'destroy']);

        // Работа с файлами в S3
        Route::prefix('files')->middleware('throttle:uploads')->group(function () {
            Route::post('/upload', [FileController::class, 'upload']);
            Route::get('/show', [FileController::class, 'show']);
            Route::get('/download', [FileController::class, 'download']);
            Route::delete('/delete', [FileController::class, 'delete']);
        });
    });
});
