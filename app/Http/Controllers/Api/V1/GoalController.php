<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoalController extends Controller
{
    // Все цели пользователя
    public function all(Request $request): JsonResponse
    {
        $workspaceIds = [$request->user()->defaultWorkspace()->id];

        $goals = Goal::whereIn('workspace_id', $workspaceIds)
            ->with(['creator', 'lifeSphere:id,name,color'])
            ->withCount(['projects', 'directTasks'])
            ->get()
            ->each(function ($goal) {
                $goal->append('progress');
            });

        return ApiResponse::success($goals, 'Список целей получен');
    }

    // Создание цели
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7',
            'status' => 'nullable|in:active,archived,completed',
            'deadline' => 'nullable|date',
            'bible_verse' => 'nullable|string|max:500',
            'life_sphere_id' => 'nullable|exists:life_spheres,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        unset($validated['image']);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        if ($request->hasFile('image')) {
            $imageData = $this->processImage($request->file('image'), $workspace->id);
            $validated['image_path'] = $imageData['path'];
            $validated['image_url'] = $imageData['url'];
        }

        $goal = Goal::create($validated);
        $goal->load(['creator', 'lifeSphere:id,name,color']);
        $goal->loadCount(['projects', 'directTasks']);
        $goal->append('progress');

        return ApiResponse::success($goal, 'Цель создана', 201);
    }

    // Получение цели
    public function show(Goal $goal): JsonResponse
    {
        $this->authorize('view', $goal);

        $goal->load(['creator', 'lifeSphere:id,name,color', 'projects.tasks', 'directTasks', 'contacts']);
        $goal->append('progress');

        return ApiResponse::success($goal, 'Цель получена');
    }

    // Обновление цели
    public function update(Request $request, Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|size:7',
            'status' => 'sometimes|in:active,archived,completed',
            'deadline' => 'nullable|date',
            'bible_verse' => 'nullable|string|max:500',
            'life_sphere_id' => 'nullable|exists:life_spheres,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $contactIds = $validated['contact_ids'] ?? null;
        unset($validated['image'], $validated['contact_ids']);

        if ($request->hasFile('image')) {
            if ($goal->image_path) {
                Storage::disk('s3')->delete($goal->image_path);
            }
            $imageData = $this->processImage($request->file('image'), $goal->workspace_id);
            $validated['image_path'] = $imageData['path'];
            $validated['image_url'] = $imageData['url'];
        }

        $goal->update($validated);

        if ($contactIds !== null) {
            $goal->contacts()->sync($contactIds);
        }

        $fresh = $goal->fresh();
        $fresh->load(['creator', 'lifeSphere:id,name,color', 'contacts']);
        $fresh->loadCount(['projects', 'directTasks']);
        $fresh->append('progress');

        return ApiResponse::success($fresh, 'Цель обновлена');
    }

    // Удаление цели
    public function destroy(Goal $goal): JsonResponse
    {
        $this->authorize('delete', $goal);

        if ($goal->image_path) {
            Storage::disk('s3')->delete($goal->image_path);
        }

        $goal->delete();

        return ApiResponse::success(null, 'Цель удалена');
    }

    // Удаление только картинки
    public function deleteImage(Goal $goal): JsonResponse
    {
        $this->authorize('update', $goal);

        if ($goal->image_path) {
            Storage::disk('s3')->delete($goal->image_path);
            $goal->update(['image_path' => null, 'image_url' => null]);
        }

        $fresh = $goal->fresh();
        $fresh->load(['creator', 'lifeSphere:id,name,color']);
        $fresh->loadCount(['projects', 'directTasks']);
        $fresh->append('progress');

        return ApiResponse::success($fresh, 'Изображение удалено');
    }

    private function processImage($file, int $workspaceId): array
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = Str::uuid() . '.' . $extension;
        $s3Path = "todo/goals/{$workspaceId}/{$filename}";

        Storage::disk('s3')->put($s3Path, \file_get_contents($file->getRealPath()));

        $bucket = config('filesystems.disks.s3.bucket');
        $endpoint = config('filesystems.disks.s3.endpoint');

        return [
            'path' => $s3Path,
            'url' => "{$endpoint}/{$bucket}/{$s3Path}",
        ];
    }
}
