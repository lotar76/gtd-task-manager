<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Birthday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BirthdayController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $birthdays = Birthday::where('workspace_id', $workspace->id)
            ->with(['creator:id,name'])
            ->orderBy('date')
            ->get();

        return ApiResponse::success($birthdays, 'Список дней рождения');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $birthday = Birthday::create($validated);
        $birthday->load(['creator:id,name']);

        return ApiResponse::success($birthday, 'День рождения добавлен', 201);
    }

    public function update(Request $request, Birthday $birthday): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'note' => 'nullable|string',
        ]);

        $birthday->update($validated);

        $fresh = $birthday->fresh();
        $fresh->load(['creator:id,name']);

        return ApiResponse::success($fresh, 'День рождения обновлен');
    }

    public function destroy(Birthday $birthday): JsonResponse
    {
        $birthday->delete();

        return ApiResponse::success(null, 'День рождения удален');
    }
}
