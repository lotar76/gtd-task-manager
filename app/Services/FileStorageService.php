<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Загрузка файла в S3.
     */
    public function upload(UploadedFile $file, string $directory = 'uploads'): array
    {
        $filename = $this->generateUniqueFilename($file);
        $path = $directory . '/' . $filename;

        $uploaded = Storage::disk('s3')->put($path, file_get_contents($file->getRealPath()));

        if (!$uploaded) {
            throw new \Exception('Failed to upload file to S3');
        }

        return [
            'path' => $path,
            'url' => Storage::disk('s3')->url($path),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Получение URL файла из S3.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk('s3')->url($path);
    }

    /**
     * Получение временного URL файла (signed URL).
     */
    public function getTemporaryUrl(string $path, int $minutes = 60): string
    {
        return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes($minutes));
    }

    /**
     * Скачивание файла из S3.
     */
    public function download(string $path): string
    {
        if (!Storage::disk('s3')->exists($path)) {
            throw new \Exception('File not found in S3');
        }

        return Storage::disk('s3')->get($path);
    }

    /**
     * Удаление файла из S3.
     */
    public function delete(string $path): bool
    {
        if (!Storage::disk('s3')->exists($path)) {
            throw new \Exception('File not found in S3');
        }

        return Storage::disk('s3')->delete($path);
    }

    /**
     * Проверка существования файла в S3.
     */
    public function exists(string $path): bool
    {
        return Storage::disk('s3')->exists($path);
    }

    /**
     * Получение информации о файле.
     */
    public function getMetadata(string $path): array
    {
        if (!Storage::disk('s3')->exists($path)) {
            throw new \Exception('File not found in S3');
        }

        return [
            'size' => Storage::disk('s3')->size($path),
            'last_modified' => Storage::disk('s3')->lastModified($path),
            'mime_type' => Storage::disk('s3')->mimeType($path),
        ];
    }

    /**
     * Генерация уникального имени файла.
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }
}

