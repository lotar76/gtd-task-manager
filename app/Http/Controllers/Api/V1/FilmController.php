<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Film;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FilmController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $query = Film::where('workspace_id', $workspace->id)
            ->with(['creator:id,name'])
            ->orderBy('updated_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $films = $query->get();

        foreach ($films as $film) {
            $this->appendPosterUrl($film);
        }

        return ApiResponse::success($films, 'Список фильмов');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'director' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:want,watching,watched',
            'rating' => 'nullable|integer|min:1|max:10',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        unset($validated['poster']);
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        if ($request->hasFile('poster')) {
            $imageData = $this->processImage($request->file('poster'), $workspace->id, 'films');
            $validated['poster_path'] = $imageData['path'];
        }

        $film = Film::create($validated);
        $film->load(['creator:id,name']);
        $this->appendPosterUrl($film);

        return ApiResponse::success($film, 'Фильм добавлен', 201);
    }

    public function update(Request $request, Film $film): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'director' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:want,watching,watched',
            'rating' => 'nullable|integer|min:1|max:10',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        unset($validated['poster']);

        if ($request->hasFile('poster')) {
            if ($film->poster_path) {
                Storage::disk('s3')->delete($film->poster_path);
            }
            $imageData = $this->processImage($request->file('poster'), $film->workspace_id, 'films');
            $validated['poster_path'] = $imageData['path'];
        }

        $film->update($validated);

        $fresh = $film->fresh();
        $fresh->load(['creator:id,name']);
        $this->appendPosterUrl($fresh);

        return ApiResponse::success($fresh, 'Фильм обновлен');
    }

    public function destroy(Film $film): JsonResponse
    {
        if ($film->poster_path) {
            Storage::disk('s3')->delete($film->poster_path);
        }

        $film->delete();

        return ApiResponse::success(null, 'Фильм удален');
    }

    public function deletePoster(Film $film): JsonResponse
    {
        if ($film->poster_path) {
            Storage::disk('s3')->delete($film->poster_path);
            $film->update(['poster_path' => null]);
        }

        $fresh = $film->fresh();
        $fresh->load(['creator:id,name']);
        $this->appendPosterUrl($fresh);

        return ApiResponse::success($fresh, 'Постер удален');
    }

    private function appendPosterUrl(Film $film): void
    {
        if ($film->poster_path) {
            $bucket = config('filesystems.disks.s3.bucket');
            $endpoint = config('filesystems.disks.s3.endpoint');
            $film->poster_url = "{$endpoint}/{$bucket}/{$film->poster_path}";
        } else {
            $film->poster_url = null;
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
