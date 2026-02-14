<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Task;
use App\Services\AiMirrorService;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly AiMirrorService $aiMirrorService,
    ) {
    }

    /**
     * Зеркало жизни — основные данные дашборда
     */
    public function getLifeMirror(Request $request): JsonResponse
    {
        $request->validate([
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'period' => 'sometimes|string|in:day,week,month,year',
        ]);

        $workspaceId = (int) $request->input('workspace_id');
        $period = $request->input('period', 'day');

        $data = $this->dashboardService->getLifeMirrorData($workspaceId, $period);

        return ApiResponse::success($data, 'Данные зеркала жизни');
    }

    /**
     * AI-сообщение для дашборда
     */
    public function getAiMessage(Request $request): JsonResponse
    {
        $request->validate([
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'period' => 'required|string|in:day,week,month,year',
        ]);

        $workspaceId = (int) $request->input('workspace_id');
        $period = $request->input('period');

        // Получаем данные для AI
        $mirrorData = $this->dashboardService->getLifeMirrorData($workspaceId, $period);

        // Вызываем AI-сервис (с кешем и fallback)
        $message = $this->aiMirrorService->getMessage($workspaceId, $period, $mirrorData);

        return ApiResponse::success($message, 'AI-сообщение');
    }

    // ─── Старый метод для обратной совместимости ──────────

    /**
     * Получить статистику для дашборда (legacy)
     */
    public function getStats(Request $request)
    {
        $workspaceId = $request->input('workspace_id');
        $period = $request->input('period', 'week');

        $now = Carbon::now();
        $startDate = match ($period) {
            'day' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            default => $now->copy()->startOfWeek(),
        };

        $query = Task::where('workspace_id', $workspaceId);

        $completedThisPeriod = (clone $query)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $startDate)
            ->count();

        $totalActiveTasks = (clone $query)
            ->whereNull('completed_at')
            ->count();

        $todayTasks = (clone $query)
            ->whereNull('completed_at')
            ->where(function ($q) {
                $q->where('status', 'today')
                  ->orWhere('due_date', Carbon::today()->toDateString());
            })
            ->count();

        $weekTasks = (clone $query)
            ->whereNull('completed_at')
            ->whereBetween('due_date', [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString()
            ])
            ->count();

        $overdueTasks = (clone $query)
            ->whereNull('completed_at')
            ->where('due_date', '<', Carbon::today()->toDateString())
            ->count();

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $completed = Task::where('workspace_id', $workspaceId)
                ->whereDate('completed_at', $date->toDateString())
                ->count();

            $chartData[] = [
                'date' => $date->format('D'),
                'count' => $completed,
            ];
        }

        $totalTasksInPeriod = (clone $query)
            ->where(function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate)
                  ->orWhere('completed_at', '>=', $startDate);
            })
            ->count();

        $productivity = $totalTasksInPeriod > 0
            ? round(($completedThisPeriod / $totalTasksInPeriod) * 100)
            : 0;

        $topProjects = Task::where('workspace_id', $workspaceId)
            ->whereNotNull('project_id')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $startDate)
            ->select('project_id', DB::raw('count(*) as completed_count'))
            ->groupBy('project_id')
            ->orderByDesc('completed_count')
            ->limit(3)
            ->with('project:id,name,color')
            ->get()
            ->map(function ($item) {
                return [
                    'project' => $item->project,
                    'count' => $item->completed_count,
                ];
            });

        return response()->json([
            'period' => $period,
            'stats' => [
                'completed_this_period' => $completedThisPeriod,
                'total_active' => $totalActiveTasks,
                'today' => $todayTasks,
                'week' => $weekTasks,
                'overdue' => $overdueTasks,
                'productivity' => $productivity,
            ],
            'chart_data' => $chartData,
            'top_projects' => $topProjects,
        ]);
    }
}
