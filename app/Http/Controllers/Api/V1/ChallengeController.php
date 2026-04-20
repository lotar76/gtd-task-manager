<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Challenge;
use App\Models\ChallengeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->input('month');
        $startDate = "{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $challenges = Challenge::where('user_id', Auth::id())
            ->with(['entries' => fn ($q) => $q->whereBetween('date', [$startDate, $endDate])])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return ApiResponse::success($challenges, 'Список челленджей');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'sometimes|in:checkbox,timer,composite',
            'timer_minutes' => 'required_if:type,timer|nullable|integer|min:1|max:480',
            'subtasks' => 'required_if:type,composite|nullable|array|min:1|max:20',
            'subtasks.*' => 'string|max:255',
        ]);

        $maxPosition = Challenge::where('user_id', Auth::id())->max('position') ?? -1;

        $challenge = Challenge::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'type' => $validated['type'] ?? 'checkbox',
            'timer_minutes' => $validated['timer_minutes'] ?? null,
            'subtasks' => $validated['subtasks'] ?? null,
            'position' => $maxPosition + 1,
        ]);

        return ApiResponse::success($challenge, 'Челлендж создан', 201);
    }

    public function update(Request $request, Challenge $challenge): JsonResponse
    {
        if ($challenge->user_id !== Auth::id()) {
            return ApiResponse::error('Доступ запрещён', 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|integer|min:0',
            'timer_minutes' => 'sometimes|nullable|integer|min:1|max:480',
            'subtasks' => 'sometimes|nullable|array|min:1|max:20',
            'subtasks.*' => 'string|max:255',
        ]);

        $challenge->update($validated);

        return ApiResponse::success($challenge, 'Челлендж обновлён');
    }

    public function destroy(Challenge $challenge): JsonResponse
    {
        if ($challenge->user_id !== Auth::id()) {
            return ApiResponse::error('Доступ запрещён', 403);
        }

        $challenge->delete();

        return ApiResponse::success(null, 'Челлендж удалён');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:challenges,id',
        ]);

        $userId = Auth::id();

        foreach ($validated['ids'] as $position => $id) {
            Challenge::where('id', $id)->where('user_id', $userId)->update(['position' => $position]);
        }

        return ApiResponse::success(null, 'Порядок обновлён');
    }

    public function toggle(Request $request, Challenge $challenge): JsonResponse
    {
        if ($challenge->user_id !== Auth::id()) {
            return ApiResponse::error('Доступ запрещён', 403);
        }

        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'subtask_index' => 'sometimes|integer|min:0',
            'timer_seconds' => 'sometimes|integer|min:0',
        ]);

        if ($validated['date'] !== now()->toDateString()) {
            return ApiResponse::error('Можно отмечать только сегодняшний день', 422);
        }

        $entry = ChallengeEntry::where('challenge_id', $challenge->id)
            ->where('date', $validated['date'])
            ->first();

        // Composite: toggle individual subtask
        if ($challenge->type === 'composite') {
            $subtaskCount = count($challenge->subtasks ?? []);
            $states = $entry?->subtask_states ?? array_fill(0, $subtaskCount, false);

            if (isset($validated['subtask_index'])) {
                $idx = $validated['subtask_index'];
                $states[$idx] = !$states[$idx];
            }

            $allDone = !in_array(false, $states, true);

            if ($entry) {
                $entry->update(['subtask_states' => $states, 'completed' => $allDone]);
            } else {
                $entry = ChallengeEntry::create([
                    'challenge_id' => $challenge->id,
                    'date' => $validated['date'],
                    'completed' => $allDone,
                    'subtask_states' => $states,
                ]);
            }

            return ApiResponse::success([
                'completed' => $allDone,
                'subtask_states' => $states,
            ], 'Подзадача обновлена');
        }

        // Timer: mark completed with elapsed seconds
        if ($challenge->type === 'timer') {
            if ($entry) {
                if ($entry->completed) {
                    $entry->delete();
                    return ApiResponse::success(['completed' => false], 'Отметка снята');
                }
                $entry->update([
                    'completed' => true,
                    'timer_seconds' => $validated['timer_seconds'] ?? $challenge->timer_minutes * 60,
                ]);
            } else {
                $entry = ChallengeEntry::create([
                    'challenge_id' => $challenge->id,
                    'date' => $validated['date'],
                    'completed' => true,
                    'timer_seconds' => $validated['timer_seconds'] ?? $challenge->timer_minutes * 60,
                ]);
            }
            return ApiResponse::success(['completed' => true], 'Таймер завершён');
        }

        // Checkbox (default)
        if ($entry) {
            if ($entry->completed) {
                $entry->delete();
                return ApiResponse::success(['completed' => false], 'Отметка снята');
            }
            $entry->update(['completed' => true]);
        } else {
            ChallengeEntry::create([
                'challenge_id' => $challenge->id,
                'date' => $validated['date'],
                'completed' => true,
            ]);
        }

        return ApiResponse::success(['completed' => true], 'День отмечен');
    }
}
