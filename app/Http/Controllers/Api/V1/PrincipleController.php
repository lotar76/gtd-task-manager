<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Principle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrincipleController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspaceId = $request->user()->defaultWorkspace()->id;

        $principles = Principle::where('workspace_id', $workspaceId)
            ->orderBy('position')
            ->get();

        return ApiResponse::success($principles, 'Список принципов получен');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'position' => 'nullable|integer|min:0',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        if (!isset($validated['position'])) {
            $validated['position'] = Principle::where('workspace_id', $workspace->id)->max('position') + 1;
        }

        $principle = Principle::create($validated);

        return ApiResponse::success($principle, 'Принцип создан', 201);
    }

    public function update(Request $request, Principle $principle): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'position' => 'nullable|integer|min:0',
        ]);

        $principle->update($validated);

        return ApiResponse::success($principle->fresh(), 'Принцип обновлён');
    }

    public function destroy(Principle $principle): JsonResponse
    {
        $principle->delete();

        return ApiResponse::success(null, 'Принцип удалён');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:principles,id',
        ]);

        foreach ($validated['ids'] as $position => $id) {
            Principle::where('id', $id)->update(['position' => $position]);
        }

        return ApiResponse::success(null, 'Порядок обновлён');
    }
}
