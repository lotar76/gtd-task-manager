<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    public function __construct(
        private readonly FileStorageService $fileStorageService
    ) {
    }

    // Загрузка файла к задаче
    public function upload(Request $request, Workspace $workspace, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        
        // Загрузка в S3
        $path = $this->fileStorageService->upload(
            $file,
            "workspaces/{$workspace->id}/tasks/{$task->id}"
        );

        // Создание записи в БД
        $attachment = $task->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => Auth::id(),
        ]);

        $attachment->load('uploader');

        return ApiResponse::success($attachment, 'Файл загружен', 201);
    }

    // Получение информации о файле
    public function show(Workspace $workspace, Attachment $attachment): JsonResponse
    {
        $this->authorize('view', $attachment->task);

        $attachment->load('uploader');

        // Добавляем URL для скачивания
        $attachment->download_url = $this->fileStorageService->getTemporaryUrl($attachment->file_path, 3600);

        return ApiResponse::success($attachment, 'Информация о файле получена');
    }

    // Скачивание файла
    public function download(Workspace $workspace, Attachment $attachment)
    {
        $this->authorize('view', $attachment->task);

        return $this->fileStorageService->download($attachment->file_path, $attachment->file_name);
    }

    // Удаление файла
    public function destroy(Workspace $workspace, Attachment $attachment): JsonResponse
    {
        $this->authorize('update', $attachment->task);

        // Удаление из S3
        $this->fileStorageService->delete($attachment->file_path);

        // Удаление записи из БД
        $attachment->delete();

        return ApiResponse::success(null, 'Файл удален');
    }
}


