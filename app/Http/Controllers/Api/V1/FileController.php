<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UploadFileRequest;
use App\Http\Responses\ApiResponse;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(
        protected FileStorageService $fileStorageService
    ) {
    }

    /**
     * Загрузка файла в S3.
     */
    public function upload(UploadFileRequest $request): JsonResponse
    {
        try {
            $directory = $request->input('directory', 'uploads');
            $fileData = $this->fileStorageService->upload($request->file('file'), $directory);

            return ApiResponse::success($fileData, 'File uploaded successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Получение информации о файле.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $path = $request->input('path');

            if (!$path) {
                return ApiResponse::error('Path is required', 400);
            }

            if (!$this->fileStorageService->exists($path)) {
                return ApiResponse::error('File not found', 404);
            }

            $metadata = $this->fileStorageService->getMetadata($path);
            $url = $this->fileStorageService->getUrl($path);
            $temporaryUrl = $this->fileStorageService->getTemporaryUrl($path);

            return ApiResponse::success([
                'path' => $path,
                'url' => $url,
                'temporary_url' => $temporaryUrl,
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Получение временного URL для скачивания файла.
     */
    public function download(Request $request): JsonResponse
    {
        try {
            $path = $request->input('path');
            $minutes = $request->input('minutes', 60);

            if (!$path) {
                return ApiResponse::error('Path is required', 400);
            }

            if (!$this->fileStorageService->exists($path)) {
                return ApiResponse::error('File not found', 404);
            }

            $url = $this->fileStorageService->getTemporaryUrl($path, $minutes);

            return ApiResponse::success([
                'download_url' => $url,
                'expires_in_minutes' => $minutes,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Удаление файла из S3.
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $path = $request->input('path');

            if (!$path) {
                return ApiResponse::error('Path is required', 400);
            }

            $this->fileStorageService->delete($path);

            return ApiResponse::success(null, 'File deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}

