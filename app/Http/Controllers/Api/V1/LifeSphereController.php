<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\LifeSphere;
use App\Models\LifeSphereImage;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class LifeSphereController extends Controller
{
    public function __construct(
        private readonly FileStorageService $fileStorage,
    ) {}

    // Все сферы пользователя
    public function all(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->defaultWorkspace()->id;

        $spheres = LifeSphere::where('workspace_id', $workspaceId)
            ->with(['images'])
            ->withCount(['tasks', 'goals'])
            ->orderBy('position')
            ->get()
            ->map(function ($sphere) {
                $this->appendImagesUrls($sphere);
                return $sphere;
            });

        return ApiResponse::success($spheres, 'Список сфер получен');
    }

    // Создание сферы
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|size:7',
            'description' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:5120',
            'position' => 'nullable|integer|min:0',
        ]);

        unset($validated['image']);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $sphere = LifeSphere::create($validated);

        if ($request->hasFile('image')) {
            $path = $this->processAndUploadImage($request->file('image'));
            $sphere->images()->create(['path' => $path, 'position' => 0]);
        }

        $sphere->load('images');
        $this->appendCoverUrl($sphere);
        $sphere->loadCount(['tasks', 'goals']);

        return ApiResponse::success($sphere, 'Сфера создана', 201);
    }

    // Получение сферы
    public function show(LifeSphere $lifeSphere): JsonResponse
    {
        $lifeSphere->load(['tasks.project', 'tasks.context', 'tasks.assignee', 'tasks.tags', 'goals', 'images']);
        $lifeSphere->loadCount(['tasks', 'goals']);

        $this->appendImagesUrls($lifeSphere);

        return ApiResponse::success($lifeSphere, 'Сфера получена');
    }

    // Обновление сферы
    public function update(Request $request, LifeSphere $lifeSphere): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|size:7',
            'description' => 'nullable|string|max:5000',
            'position' => 'nullable|integer|min:0',
            'is_hidden' => 'sometimes|boolean',
        ]);

        $lifeSphere->update($validated);

        $sphere = $lifeSphere->fresh()->load('images')->loadCount(['tasks', 'goals']);
        $this->appendImagesUrls($sphere);

        return ApiResponse::success($sphere, 'Сфера обновлена');
    }

    // Добавить картинку
    public function addImage(Request $request, LifeSphere $lifeSphere): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $maxPosition = $lifeSphere->images()->max('position') ?? -1;
        $path = $this->processAndUploadImage($request->file('image'));

        $image = $lifeSphere->images()->create([
            'path' => $path,
            'position' => $maxPosition + 1,
        ]);

        $image->url = $this->fileStorage->getUrl($image->path);

        return ApiResponse::success($image, 'Картинка добавлена', 201);
    }

    // Удалить картинку
    public function deleteImage(LifeSphere $lifeSphere, LifeSphereImage $image): JsonResponse
    {
        try { $this->fileStorage->delete($image->path); } catch (\Throwable) {}
        $image->delete();

        return ApiResponse::success(null, 'Картинка удалена');
    }

    // Сортировка картинок
    public function reorderImages(Request $request, LifeSphere $lifeSphere): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        foreach ($validated['ids'] as $position => $id) {
            $lifeSphere->images()->where('id', $id)->update(['position' => $position]);
        }

        return ApiResponse::success(null, 'Порядок картинок обновлён');
    }

    // Удаление сферы
    public function destroy(LifeSphere $lifeSphere): JsonResponse
    {
        $tasksCount = $lifeSphere->tasks()->count();
        $goalsCount = $lifeSphere->goals()->count();

        if ($tasksCount > 0 || $goalsCount > 0) {
            $parts = [];
            if ($tasksCount > 0) $parts[] = "задачами ({$tasksCount})";
            if ($goalsCount > 0) $parts[] = "целями ({$goalsCount})";
            return ApiResponse::error(
                "Нельзя удалить сферу с привязанными " . implode(' и ', $parts) . ". Сначала открепите их или скройте сферу.",
                422
            );
        }

        foreach ($lifeSphere->images as $image) {
            try { $this->fileStorage->delete($image->path); } catch (\Throwable) {}
        }

        $lifeSphere->delete();

        return ApiResponse::success(null, 'Сфера удалена');
    }

    // Заполнить дефолтными сферами
    public function seed(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $defaults = [
            ['name' => 'Духовная', 'color' => '#8B5CF6', 'position' => 0],
            ['name' => 'Семья', 'color' => '#EC4899', 'position' => 1],
            ['name' => 'Здоровье', 'color' => '#10B981', 'position' => 2],
            ['name' => 'Финансы', 'color' => '#F59E0B', 'position' => 3],
            ['name' => 'Работа', 'color' => '#3B82F6', 'position' => 4],
            ['name' => 'Развитие', 'color' => '#6366F1', 'position' => 5],
            ['name' => 'Окружение', 'color' => '#14B8A6', 'position' => 6],
            ['name' => 'Отдых', 'color' => '#F97316', 'position' => 7],
        ];

        $created = [];
        foreach ($defaults as $sphere) {
            $created[] = LifeSphere::create([
                ...$sphere,
                'workspace_id' => $workspace->id,
                'created_by' => Auth::id(),
            ]);
        }

        return ApiResponse::success($created, 'Сферы по умолчанию созданы', 201);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:life_spheres,id',
        ]);

        foreach ($validated['ids'] as $position => $id) {
            LifeSphere::where('id', $id)->update(['position' => $position]);
        }

        return ApiResponse::success(null, 'Порядок обновлён');
    }

    private function processAndUploadImage($file): string
    {
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file->getRealPath());

        $webpContent = $image->toWebp(80)->toString();

        $filename = Str::uuid() . '.webp';
        $path = 'spheres/' . $filename;

        Storage::disk(config('filesystems.default'))
            ->put($path, $webpContent);

        return $path;
    }

    private function appendCoverUrl(LifeSphere $sphere): LifeSphere
    {
        $cover = $sphere->coverImage;
        $sphere->cover_image_url = $cover ? $this->fileStorage->getUrl($cover->path) : null;
        return $sphere;
    }

    private function appendImagesUrls(LifeSphere $sphere): void
    {
        $sphere->cover_image_url = null;
        foreach ($sphere->images as $image) {
            $image->url = $this->fileStorage->getUrl($image->path);
        }
        if ($sphere->images->isNotEmpty()) {
            $sphere->cover_image_url = $sphere->images->first()->url;
        }
    }
}
