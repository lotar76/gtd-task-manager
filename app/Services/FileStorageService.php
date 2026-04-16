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

        try {
            $uploaded = Storage::disk(config('filesystems.default'))->put($path, file_get_contents($file->getRealPath()));
        } catch (\Throwable $e) {
            \Log::error('S3 upload threw', ['path' => $path, 'error' => $e->getMessage()]);
            throw new \Exception('Failed to upload file to S3: ' . $e->getMessage(), 0, $e);
        }

        if (!$uploaded) {
            throw new \Exception('Failed to upload file to S3 (put returned false)');
        }

        return [
            'path' => $path,
            'url' => Storage::disk(config('filesystems.default'))->url($path),
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
        return Storage::disk(config('filesystems.default'))->url($path);
    }

    /**
     * Получение временного URL файла (signed URL).
     */
    public function getTemporaryUrl(string $path, int $minutes = 60): string
    {
        return Storage::disk(config('filesystems.default'))->temporaryUrl($path, now()->addMinutes($minutes));
    }

    /**
     * Скачивание файла из S3.
     */
    public function download(string $path): string
    {
        if (!Storage::disk(config('filesystems.default'))->exists($path)) {
            throw new \Exception('File not found in S3');
        }

        return Storage::disk(config('filesystems.default'))->get($path);
    }

    /**
     * Удаление файла из S3.
     */
    public function delete(string $path): bool
    {
        if (!Storage::disk(config('filesystems.default'))->exists($path)) {
            throw new \Exception('File not found in S3');
        }

        return Storage::disk(config('filesystems.default'))->delete($path);
    }

    /**
     * Проверка существования файла в S3.
     */
    public function exists(string $path): bool
    {
        return Storage::disk(config('filesystems.default'))->exists($path);
    }

    /**
     * Получение информации о файле.
     */
    public function getMetadata(string $path): array
    {
        if (!Storage::disk(config('filesystems.default'))->exists($path)) {
            throw new \Exception('File not found in S3');
        }

        return [
            'size' => Storage::disk(config('filesystems.default'))->size($path),
            'last_modified' => Storage::disk(config('filesystems.default'))->lastModified($path),
            'mime_type' => Storage::disk(config('filesystems.default'))->mimeType($path),
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

