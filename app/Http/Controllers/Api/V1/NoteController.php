<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $workspace = $request->user()->defaultWorkspace();

        $query = Note::where('workspace_id', $workspace->id)
            ->with(['creator:id,name', 'goal:id,name', 'task:id,title'])
            ->orderBy('updated_at', 'desc');

        if ($request->has('goal_id')) {
            $query->where('goal_id', $request->goal_id);
        }

        if ($request->has('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        $notes = $query->get();

        return ApiResponse::success($notes, 'Список заметок');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'goal_id' => 'nullable|exists:goals,id',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $workspace = $request->user()->defaultWorkspace();
        $validated['workspace_id'] = $workspace->id;
        $validated['created_by'] = Auth::id();

        $note = Note::create($validated);
        $note->load(['creator:id,name', 'goal:id,name', 'task:id,title']);

        return ApiResponse::success($note, 'Заметка создана', 201);
    }

    public function show(Note $note): JsonResponse
    {
        $note->load(['creator:id,name', 'goal:id,name', 'task:id,title']);

        return ApiResponse::success($note, 'Заметка получена');
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'goal_id' => 'nullable|exists:goals,id',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $note->update($validated);

        $fresh = $note->fresh();
        $fresh->load(['creator:id,name', 'goal:id,name', 'task:id,title']);

        return ApiResponse::success($fresh, 'Заметка обновлена');
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return ApiResponse::success(null, 'Заметка удалена');
    }
}
