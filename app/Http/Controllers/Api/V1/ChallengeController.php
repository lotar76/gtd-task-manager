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
        ]);

        $maxPosition = Challenge::where('user_id', Auth::id())->max('position') ?? -1;

        $challenge = Challenge::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
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

    public function toggle(Request $request, Challenge $challenge): JsonResponse
    {
        if ($challenge->user_id !== Auth::id()) {
            return ApiResponse::error('Доступ запрещён', 403);
        }

        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validated['date'] !== now()->toDateString()) {
            return ApiResponse::error('Можно отмечать только сегодняшний день', 422);
        }

        $entry = ChallengeEntry::where('challenge_id', $challenge->id)
            ->where('date', $validated['date'])
            ->first();

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
