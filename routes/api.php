<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttachmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\ContextController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\WorkspaceController;

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

        // === WORKSPACES (Команды) ===
        Route::apiResource('workspaces', WorkspaceController::class);
        Route::get('workspaces/{workspace}/members', [WorkspaceController::class, 'members']);
        Route::post('workspaces/{workspace}/members', [WorkspaceController::class, 'addMember']);
        Route::delete('workspaces/{workspace}/members/{user}', [WorkspaceController::class, 'removeMember']);
        Route::put('workspaces/{workspace}/members/{user}/role', [WorkspaceController::class, 'updateMemberRole']);

        // === В КОНТЕКСТЕ WORKSPACE ===
        Route::prefix('workspaces/{workspace}')->group(function () {
            
            // GTD Виды задач
            Route::get('inbox', [TaskController::class, 'inbox']);
            Route::get('next-actions', [TaskController::class, 'nextActions']);
            Route::get('waiting', [TaskController::class, 'waiting']);
            Route::get('someday', [TaskController::class, 'someday']);
            Route::get('today', [TaskController::class, 'today']);
            Route::get('tomorrow', [TaskController::class, 'tomorrow']);
            Route::get('my-tasks', [TaskController::class, 'myTasks']);
            Route::get('calendar', [TaskController::class, 'calendar']);
            Route::get('counts', [TaskController::class, 'counts']);

            // CRUD Задач
            Route::apiResource('tasks', TaskController::class);
            Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);
            Route::post('tasks/{task}/uncomplete', [TaskController::class, 'uncomplete']);
            Route::post('tasks/{task}/move', [TaskController::class, 'move']);
            Route::post('tasks/{task}/assign', [TaskController::class, 'assign']);

            // Проекты
            Route::apiResource('projects', ProjectController::class);
            Route::get('projects/{project}/tasks', [ProjectController::class, 'tasks']);

            // Цели
            Route::apiResource('goals', GoalController::class);

            // Контексты
            Route::apiResource('contexts', ContextController::class);

            // Теги
            Route::apiResource('tags', TagController::class);

            // Комментарии
            Route::get('tasks/{task}/comments', [CommentController::class, 'index']);
            Route::post('tasks/{task}/comments', [CommentController::class, 'store']);
            Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

            // Вложения
            Route::post('tasks/{task}/attachments', [AttachmentController::class, 'upload']);
            Route::get('attachments/{attachment}', [AttachmentController::class, 'show']);
            Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download']);
            Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);
        });

        // Работа с файлами в S3 (старый функционал)
        Route::prefix('files')->middleware('throttle:uploads')->group(function () {
            Route::post('/upload', [FileController::class, 'upload']);
            Route::get('/show', [FileController::class, 'show']);
            Route::get('/download', [FileController::class, 'download']);
            Route::delete('/delete', [FileController::class, 'delete']);
        });
    });
});

