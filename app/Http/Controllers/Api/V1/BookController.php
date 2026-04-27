<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $query = Book::where('workspace_id', $workspace->id)
            ->with(['creator:id,name'])
            ->orderBy('updated_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->get();

        foreach ($books as $book) {
            $this->appendCoverUrl($book);
        }

        return ApiResponse::success($books, 'Список книг');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'annotation' => 'nullable|string',
            'status' => 'required|in:want,reading,read',
            'rating' => 'nullable|integer|min:1|max:10',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        unset($validated['cover']);
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        if ($request->hasFile('cover')) {
            $imageData = $this->processImage($request->file('cover'), $workspace->id, 'books');
            $validated['cover_path'] = $imageData['path'];
        }

        $book = Book::create($validated);
        $book->load(['creator:id,name']);
        $this->appendCoverUrl($book);

        return ApiResponse::success($book, 'Книга добавлена', 201);
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'author' => 'nullable|string|max:255',
            'annotation' => 'nullable|string',
            'status' => 'sometimes|in:want,reading,read',
            'rating' => 'nullable|integer|min:1|max:10',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        unset($validated['cover']);

        if ($request->hasFile('cover')) {
            if ($book->cover_path) {
                Storage::disk('s3')->delete($book->cover_path);
            }
            $imageData = $this->processImage($request->file('cover'), $book->workspace_id, 'books');
            $validated['cover_path'] = $imageData['path'];
        }

        $book->update($validated);

        $fresh = $book->fresh();
        $fresh->load(['creator:id,name']);
        $this->appendCoverUrl($fresh);

        return ApiResponse::success($fresh, 'Книга обновлена');
    }

    public function destroy(Book $book): JsonResponse
    {
        if ($book->cover_path) {
            Storage::disk('s3')->delete($book->cover_path);
        }

        $book->delete();

        return ApiResponse::success(null, 'Книга удалена');
    }

    public function deleteCover(Book $book): JsonResponse
    {
        if ($book->cover_path) {
            Storage::disk('s3')->delete($book->cover_path);
            $book->update(['cover_path' => null]);
        }

        $fresh = $book->fresh();
        $fresh->load(['creator:id,name']);
        $this->appendCoverUrl($fresh);

        return ApiResponse::success($fresh, 'Обложка удалена');
    }

    private function appendCoverUrl(Book $book): void
    {
        if ($book->cover_path) {
            $bucket = config('filesystems.disks.s3.bucket');
            $endpoint = config('filesystems.disks.s3.endpoint');
            $book->cover_url = "{$endpoint}/{$bucket}/{$book->cover_path}";
        } else {
            $book->cover_url = null;
        }
    }

    private function processImage($file, int $workspaceId, string $folder): array
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = Str::uuid() . '.' . $extension;
        $s3Path = "todo/{$folder}/{$workspaceId}/{$filename}";

        Storage::disk('s3')->put($s3Path, \file_get_contents($file->getRealPath()));

        return ['path' => $s3Path];
    }
}
