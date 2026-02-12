<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Получить статистику для дашборда
     */
    public function getStats(Request $request)
    {
        $workspaceId = $request->input('workspace_id');
        $period = $request->input('period', 'week'); // day, week, month

        // Определяем диапазон дат
        $now = Carbon::now();
        $startDate = match ($period) {
            'day' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(),
            'month' => $now->copy()->startOfMonth(),
            default => $now->copy()->startOfWeek(),
        };

        // Базовый запрос
        $query = Task::where('workspace_id', $workspaceId);

        // Статистика за текущий период
        $completedThisPeriod = (clone $query)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $startDate)
            ->count();

        // Всего задач (активных)
        $totalActiveTasks = (clone $query)
            ->whereNull('completed_at')
            ->count();

        // Задачи на сегодня
        $todayTasks = (clone $query)
            ->whereNull('completed_at')
            ->where(function ($q) {
                $q->where('status', 'today')
                  ->orWhere('due_date', Carbon::today()->toDateString());
            })
            ->count();

        // Задачи на неделю
        $weekTasks = (clone $query)
            ->whereNull('completed_at')
            ->whereBetween('due_date', [
                Carbon::now()->startOfWeek()->toDateString(),
                Carbon::now()->endOfWeek()->toDateString()
            ])
            ->count();

        // Просроченные задачи
        $overdueTasks = (clone $query)
            ->whereNull('completed_at')
            ->where('due_date', '<', Carbon::today()->toDateString())
            ->count();

        // График за последние 7 дней
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

        // Продуктивность (процент выполненных от общего числа за период)
        $totalTasksInPeriod = (clone $query)
            ->where(function ($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate)
                  ->orWhere('completed_at', '>=', $startDate);
            })
            ->count();

        $productivity = $totalTasksInPeriod > 0
            ? round(($completedThisPeriod / $totalTasksInPeriod) * 100)
            : 0;

        // Топ проекты
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
