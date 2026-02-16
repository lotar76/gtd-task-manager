<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Goal;
use App\Models\LifeSphere;
use App\Models\Project;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Получить данные дашборда «Зеркало жизни»
     */
    public function getLifeMirrorData(int $workspaceId, string $period): array
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $spheres = LifeSphere::where('workspace_id', $workspaceId)
            ->orderBy('position')
            ->get();

        if ($spheres->isEmpty()) {
            return $this->emptyResponse($period);
        }

        return match ($period) {
            'day' => $this->buildDayData($workspaceId, $spheres),
            'week' => $this->buildWeekData($workspaceId, $spheres),
            'month' => $this->buildMonthData($workspaceId, $spheres),
            'year' => $this->buildYearData($workspaceId, $spheres),
            default => $this->buildDayData($workspaceId, $spheres),
        };
    }

    // ─── DAY ──────────────────────────────────────────────

    private function buildDayData(int $workspaceId, Collection $spheres): array
    {
        $today = Carbon::today()->toDateString();

        // Задачи на сегодня с определённой сферой
        $tasks = $this->getTasksWithSpheres($workspaceId)
            ->where(function ($q) use ($today) {
                $q->where('tasks.status', 'today')
                    ->orWhere('tasks.due_date', $today);
            })
            ->whereNull('tasks.completed_at')
            ->get();

        // Выполненные сегодня
        $doneTasks = $this->getTasksWithSpheres($workspaceId)
            ->whereDate('tasks.completed_at', $today)
            ->get();

        $allTasks = $tasks->merge($doneTasks)->unique('task_id');

        // Группируем по сферам
        $sphereData = [];
        $activeSphereIds = [];

        foreach ($spheres as $sphere) {
            $sphereTasks = $allTasks->where('resolved_sphere_id', $sphere->id);
            $total = $sphereTasks->count();
            $done = $sphereTasks->whereNotNull('completed_at')->count();

            if ($total > 0) {
                $activeSphereIds[] = $sphere->id;
                $sphereData[] = [
                    'id' => $sphere->id,
                    'name' => $sphere->name,
                    'color' => $sphere->color,
                    'tasks_total' => $total,
                    'tasks_done' => $done,
                    'days_without_attention' => $this->getDaysWithoutAttention($workspaceId, $sphere->id),
                    'tasks' => $sphereTasks->map(fn($t) => [
                        'id' => $t->task_id,
                        'title' => $t->title,
                        'description' => $t->description,
                        'status' => $t->status,
                        'priority' => $t->priority,
                        'due_date' => $t->due_date,
                        'completed_at' => $t->completed_at,
                        'estimated_time' => $t->estimated_time,
                        'end_time' => $t->end_time,
                        'workspace_id' => $t->workspace_id,
                        'project_id' => $t->project_id,
                        'goal_id' => $t->goal_id,
                        'life_sphere_id' => $t->life_sphere_id,
                    ])->values()->toArray(),
                ];
            }
        }

        // Задачи без сферы
        $tasksWithoutSphere = $allTasks->whereNull('resolved_sphere_id');
        $tasksWithoutSphereData = $tasksWithoutSphere->map(fn($t) => [
            'id' => $t->task_id,
            'title' => $t->title,
            'description' => $t->description,
            'status' => $t->status,
            'priority' => $t->priority,
            'due_date' => $t->due_date,
            'completed_at' => $t->completed_at,
            'estimated_time' => $t->estimated_time,
            'end_time' => $t->end_time,
            'workspace_id' => $t->workspace_id,
            'project_id' => $t->project_id,
            'goal_id' => $t->goal_id,
            'life_sphere_id' => $t->life_sphere_id,
        ])->values()->toArray();

        // Пропущенные сферы
        $missingSpheres = $spheres
            ->whereNotIn('id', $activeSphereIds)
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'color' => $s->color,
                'days_without_attention' => $this->getDaysWithoutAttention($workspaceId, $s->id),
            ])
            ->values()
            ->toArray();

        $totalTasks = $allTasks->count();
        $totalDone = $allTasks->whereNotNull('completed_at')->count();

        return [
            'period' => 'day',
            'date' => $today,
            'spheres' => $sphereData,
            'tasks_without_sphere' => $tasksWithoutSphereData,
            'missing_spheres' => $missingSpheres,
            'goals' => $this->getGoalsWithProgress($workspaceId),
            'summary' => [
                'total_tasks' => $totalTasks,
                'completed' => $totalDone,
                'spheres_active' => count($activeSphereIds),
                'spheres_total' => $spheres->count(),
                'completion_rate' => $totalTasks > 0 ? round(($totalDone / $totalTasks) * 100) : 0,
            ],
            'balance_index' => $this->calculateBalanceIndex($spheres, $allTasks, 'resolved_sphere_id'),
        ];
    }

    // ─── WEEK ─────────────────────────────────────────────

    private function buildWeekData(int $workspaceId, Collection $spheres): array
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEnd = Carbon::now()->endOfWeek()->toDateString();

        // Все задачи недели (с due_date или completed_at на этой неделе)
        $tasks = $this->getTasksWithSpheres($workspaceId)
            ->where(function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('tasks.due_date', [$weekStart, $weekEnd])
                    ->orWhereBetween('tasks.completed_at', [$weekStart . ' 00:00:00', $weekEnd . ' 23:59:59']);
            })
            ->get();

        // Разбивка по дням
        $dailyBreakdown = [];
        $dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->startOfWeek()->addDays($i);
            $dateStr = $date->toDateString();
            $dayTasks = $tasks->filter(function ($t) use ($dateStr) {
                return ($t->due_date === $dateStr)
                    || (isset($t->completed_at) && Carbon::parse($t->completed_at)->toDateString() === $dateStr);
            });

            $bySphere = [];
            foreach ($dayTasks->groupBy('resolved_sphere_id') as $sphereId => $group) {
                if ($sphereId) {
                    $bySphere[(int) $sphereId] = $group->count();
                }
            }
            $dailyBreakdown[] = [
                'day' => $dayNames[$i],
                'date' => $dateStr,
                'by_sphere' => $bySphere,
            ];
        }

        // Распределение внимания по сферам
        $sphereTasksWithSphere = $tasks->whereNotNull('resolved_sphere_id');
        $totalWithSphere = $sphereTasksWithSphere->count();
        $attentionDistribution = [];
        $activeSphereIds = [];

        foreach ($spheres as $sphere) {
            $count = $sphereTasksWithSphere->where('resolved_sphere_id', $sphere->id)->count();
            $pct = $totalWithSphere > 0 ? round(($count / $totalWithSphere) * 100) : 0;
            $attentionDistribution[] = [
                'sphere_id' => $sphere->id,
                'name' => $sphere->name,
                'color' => $sphere->color,
                'percentage' => $pct,
                'tasks_count' => $count,
            ];
            if ($count > 0) {
                $activeSphereIds[] = $sphere->id;
            }
        }

        // Сортируем по проценту убывания
        usort($attentionDistribution, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        // Проекты/потоки с прогрессом
        $projects = $this->getProjectsWithProgress($workspaceId, $spheres, 7);

        $totalTasks = $tasks->count();
        $doneTasks = $tasks->whereNotNull('completed_at')->count();

        $missingSpheres = $spheres
            ->whereNotIn('id', $activeSphereIds)
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'color' => $s->color,
                'days_without_attention' => $this->getDaysWithoutAttention($workspaceId, $s->id),
            ])
            ->values()
            ->toArray();

        return [
            'period' => 'week',
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'daily_breakdown' => $dailyBreakdown,
            'attention_distribution' => $attentionDistribution,
            'projects' => $projects,
            'missing_spheres' => $missingSpheres,
            'goals' => $this->getGoalsWithProgress($workspaceId),
            'summary' => [
                'total_tasks' => $totalTasks,
                'completed' => $doneTasks,
                'spheres_active' => count($activeSphereIds),
                'spheres_total' => $spheres->count(),
                'completion_rate' => $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0,
            ],
            'balance_index' => $this->calculateBalanceIndex($spheres, $tasks, 'resolved_sphere_id'),
        ];
    }

    // ─── MONTH ────────────────────────────────────────────

    private function buildMonthData(int $workspaceId, Collection $spheres): array
    {
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $prevMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        // Задачи текущего месяца
        $tasks = $this->getTasksWithSpheres($workspaceId)
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('tasks.due_date', [$monthStart, $monthEnd])
                    ->orWhereBetween('tasks.completed_at', [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])
                    ->orWhere(function ($q2) use ($monthStart) {
                        $q2->where('tasks.created_at', '>=', $monthStart)
                            ->whereNull('tasks.completed_at');
                    });
            })
            ->get();

        // Задачи прошлого месяца
        $prevTasks = $this->getTasksWithSpheres($workspaceId)
            ->where(function ($q) use ($prevMonthStart, $prevMonthEnd) {
                $q->whereBetween('tasks.due_date', [$prevMonthStart, $prevMonthEnd])
                    ->orWhereBetween('tasks.completed_at', [$prevMonthStart . ' 00:00:00', $prevMonthEnd . ' 23:59:59']);
            })
            ->get();

        // План vs факт
        $planned = $tasks->count();
        $done = $tasks->whereNotNull('completed_at')->count();
        $daysPassed = Carbon::now()->startOfMonth()->diffInDays(Carbon::now());
        $daysRemaining = Carbon::now()->diffInDays(Carbon::now()->endOfMonth());
        $requiredDailyPace = $daysRemaining > 0 ? round(($planned - $done) / $daysRemaining, 1) : 0;

        // Тренды сфер
        $sphereTrends = [];
        $currWithSphere = $tasks->whereNotNull('resolved_sphere_id');
        $prevWithSphere = $prevTasks->whereNotNull('resolved_sphere_id');
        $currTotal = max($currWithSphere->count(), 1);
        $prevTotal = max($prevWithSphere->count(), 1);

        foreach ($spheres as $sphere) {
            $currCount = $currWithSphere->where('resolved_sphere_id', $sphere->id)->count();
            $prevCount = $prevWithSphere->where('resolved_sphere_id', $sphere->id)->count();
            $currPct = round(($currCount / $currTotal) * 100);
            $prevPct = round(($prevCount / $prevTotal) * 100);
            $change = $currPct - $prevPct;

            $sphereTrends[] = [
                'sphere_id' => $sphere->id,
                'name' => $sphere->name,
                'color' => $sphere->color,
                'current_pct' => $currPct,
                'prev_pct' => $prevPct,
                'change' => $change,
                'direction' => $change > 2 ? 'growing' : ($change < -2 ? 'falling' : 'stable'),
            ];
        }

        // Сортируем по изменению (лучшие сверху)
        usort($sphereTrends, fn($a, $b) => $b['change'] <=> $a['change']);

        // Застрявшие проекты
        $stalledProjects = $this->getStalledProjects($workspaceId, $spheres, 14);

        // Цели под угрозой
        $goalsAtRisk = $this->getGoalsAtRisk($workspaceId, $spheres);

        return [
            'period' => 'month',
            'month' => Carbon::now()->format('Y-m'),
            'planned_vs_done' => [
                'planned' => $planned,
                'done' => $done,
                'days_passed' => $daysPassed,
                'days_remaining' => $daysRemaining,
                'required_daily_pace' => $requiredDailyPace,
                'completion_rate' => $planned > 0 ? round(($done / $planned) * 100) : 0,
            ],
            'sphere_trends' => $sphereTrends,
            'stalled_projects' => $stalledProjects,
            'goals_at_risk' => $goalsAtRisk,
            'goals' => $this->getGoalsWithProgress($workspaceId),
            'missing_spheres' => [],
            'summary' => [
                'total_tasks' => $planned,
                'completed' => $done,
                'spheres_active' => count(array_unique($currWithSphere->pluck('resolved_sphere_id')->filter()->toArray())),
                'spheres_total' => $spheres->count(),
                'completion_rate' => $planned > 0 ? round(($done / $planned) * 100) : 0,
            ],
            'balance_index' => $this->calculateBalanceIndex($spheres, $tasks, 'resolved_sphere_id'),
        ];
    }

    // ─── YEAR ─────────────────────────────────────────────

    private function buildYearData(int $workspaceId, Collection $spheres): array
    {
        $yearStart = Carbon::now()->startOfYear()->toDateString();

        // Цели
        $goals = Goal::where('workspace_id', $workspaceId)
            ->whereNotNull('life_sphere_id')
            ->get();

        $achievedGoals = $goals->where('status', 'completed')->map(fn($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'sphere_id' => $g->life_sphere_id,
            'sphere_name' => $spheres->firstWhere('id', $g->life_sphere_id)?->name,
            'sphere_color' => $spheres->firstWhere('id', $g->life_sphere_id)?->color,
            'completed_at' => $g->updated_at?->format('Y-m-d'),
        ])->values()->toArray();

        $activeGoals = $goals->where('status', '!=', 'completed')->map(function ($g) use ($spheres) {
            $progress = $g->progress;
            $daysLeft = $g->deadline ? Carbon::now()->diffInDays($g->deadline, false) : null;

            // Определяем статус
            $status = 'on_track';
            if ($g->deadline) {
                $totalDays = $g->created_at->diffInDays($g->deadline);
                $elapsed = $g->created_at->diffInDays(Carbon::now());
                $expectedProgress = $totalDays > 0 ? round(($elapsed / $totalDays) * 100) : 100;

                if ($progress == 0 && $elapsed > 30) {
                    $status = 'stalled';
                } elseif ($progress < $expectedProgress * 0.5) {
                    $status = 'at_risk';
                } elseif ($progress < $expectedProgress * 0.8) {
                    $status = 'behind';
                } else {
                    $status = $progress > $expectedProgress ? 'ahead' : 'on_track';
                }
            }

            return [
                'id' => $g->id,
                'name' => $g->name,
                'sphere_id' => $g->life_sphere_id,
                'sphere_name' => $spheres->firstWhere('id', $g->life_sphere_id)?->name,
                'sphere_color' => $spheres->firstWhere('id', $g->life_sphere_id)?->color,
                'progress' => $progress,
                'days_left' => max(0, (int) $daysLeft),
                'deadline' => $g->deadline?->format('Y-m-d'),
                'status' => $status,
            ];
        })->values()->toArray();

        // Итоги целей
        $goalsSummary = [
            'total' => $goals->count(),
            'achieved' => $goals->where('status', 'completed')->count(),
            'on_track' => collect($activeGoals)->whereIn('status', ['on_track', 'ahead'])->count(),
            'behind' => collect($activeGoals)->where('status', 'behind')->count(),
            'at_risk' => collect($activeGoals)->where('status', 'at_risk')->count(),
            'stalled' => collect($activeGoals)->where('status', 'stalled')->count(),
        ];

        // Тренд баланса по месяцам (последние 6 месяцев)
        $monthlyBalanceTrend = $this->getMonthlyBalanceTrend($workspaceId, $spheres, 6);

        // Годовые тренды сфер
        $sphereYearlyTrends = $this->getSphereYearlyTrends($workspaceId, $spheres);

        return [
            'period' => 'year',
            'year' => Carbon::now()->year,
            'goals_summary' => $goalsSummary,
            'achieved_goals' => $achievedGoals,
            'active_goals' => $activeGoals,
            'goals' => $this->getGoalsWithProgress($workspaceId),
            'monthly_balance_trend' => $monthlyBalanceTrend,
            'sphere_yearly_trends' => $sphereYearlyTrends,
            'missing_spheres' => [],
            'summary' => [
                'total_goals' => $goals->count(),
                'spheres_total' => $spheres->count(),
            ],
            'balance_index' => $monthlyBalanceTrend ? (end($monthlyBalanceTrend)['balance_index'] ?? 0) : 0,
        ];
    }

    // ─── HELPERS ──────────────────────────────────────────

    /**
     * Базовый запрос задач с определением сферы через JOIN
     */
    private function getTasksWithSpheres(int $workspaceId)
    {
        return DB::table('tasks')
            ->leftJoin('goals as direct_goal', 'tasks.goal_id', '=', 'direct_goal.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->leftJoin('goals as project_goal', 'projects.goal_id', '=', 'project_goal.id')
            ->where('tasks.workspace_id', $workspaceId)
            ->select([
                'tasks.id as task_id',
                'tasks.title',
                'tasks.description',
                'tasks.status',
                'tasks.priority',
                'tasks.due_date',
                'tasks.completed_at',
                'tasks.created_at',
                'tasks.workspace_id',
                'tasks.project_id',
                'tasks.goal_id',
                'tasks.life_sphere_id',
                'tasks.estimated_time',
                'tasks.end_time',
                DB::raw('COALESCE(tasks.life_sphere_id, direct_goal.life_sphere_id, project_goal.life_sphere_id) as resolved_sphere_id'),
            ]);
    }

    /**
     * Дней без внимания к сфере
     */
    private function getDaysWithoutAttention(int $workspaceId, int $sphereId): ?int
    {
        $lastCompleted = DB::table('tasks')
            ->leftJoin('goals as direct_goal', 'tasks.goal_id', '=', 'direct_goal.id')
            ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
            ->leftJoin('goals as project_goal', 'projects.goal_id', '=', 'project_goal.id')
            ->where('tasks.workspace_id', $workspaceId)
            ->whereNotNull('tasks.completed_at')
            ->whereRaw('COALESCE(tasks.life_sphere_id, direct_goal.life_sphere_id, project_goal.life_sphere_id) = ?', [$sphereId])
            ->max('tasks.completed_at');

        if (!$lastCompleted) {
            return null;
        }

        return (int) Carbon::parse($lastCompleted)->diffInDays(Carbon::now());
    }

    /**
     * Проекты с прогрессом и статусом
     */
    private function getProjectsWithProgress(int $workspaceId, Collection $spheres, int $stalledDays): array
    {
        $projects = Project::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->whereNotNull('goal_id')
            ->with('goal:id,name,life_sphere_id')
            ->get();

        return $projects->map(function ($project) use ($spheres, $stalledDays) {
            $sphereId = $project->goal?->life_sphere_id;
            $sphere = $spheres->firstWhere('id', $sphereId);

            $totalTasks = $project->tasks()->count();
            $doneTasks = $project->tasks()->whereNotNull('completed_at')->count();

            $lastCompleted = $project->tasks()->max('completed_at');
            $daysSinceActivity = $lastCompleted
                ? (int) Carbon::parse($lastCompleted)->diffInDays(Carbon::now())
                : null;

            $isStalled = $daysSinceActivity === null || $daysSinceActivity >= $stalledDays;

            return [
                'id' => $project->id,
                'name' => $project->name,
                'color' => $project->color,
                'sphere_id' => $sphereId,
                'sphere_name' => $sphere?->name,
                'sphere_color' => $sphere?->color,
                'goal_name' => $project->goal?->name,
                'done' => $doneTasks,
                'total' => $totalTasks,
                'is_stalled' => $isStalled && $totalTasks > 0,
                'days_since_activity' => $daysSinceActivity,
            ];
        })->filter(fn($p) => $p['total'] > 0)->values()->toArray();
    }

    /**
     * Застрявшие проекты
     */
    private function getStalledProjects(int $workspaceId, Collection $spheres, int $stalledDays): array
    {
        $allProjects = $this->getProjectsWithProgress($workspaceId, $spheres, $stalledDays);
        return array_values(array_filter($allProjects, fn($p) => $p['is_stalled']));
    }

    /**
     * Цели под угрозой
     */
    private function getGoalsAtRisk(int $workspaceId, Collection $spheres): array
    {
        $goals = Goal::where('workspace_id', $workspaceId)
            ->whereNotNull('life_sphere_id')
            ->whereNotNull('deadline')
            ->where('status', '!=', 'completed')
            ->where('deadline', '>', Carbon::now())
            ->get();

        $atRisk = [];
        foreach ($goals as $goal) {
            $progress = $goal->progress;
            $totalDays = $goal->created_at->diffInDays($goal->deadline);
            $elapsed = $goal->created_at->diffInDays(Carbon::now());
            $expectedProgress = $totalDays > 0 ? round(($elapsed / $totalDays) * 100) : 100;

            if ($progress < $expectedProgress * 0.8) {
                $sphere = $spheres->firstWhere('id', $goal->life_sphere_id);
                $daysLeft = (int) Carbon::now()->diffInDays($goal->deadline, false);

                $atRisk[] = [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'sphere_id' => $goal->life_sphere_id,
                    'sphere_name' => $sphere?->name,
                    'sphere_color' => $sphere?->color,
                    'progress' => $progress,
                    'expected_progress' => (int) $expectedProgress,
                    'days_left' => max(0, $daysLeft),
                    'deadline' => $goal->deadline->format('Y-m-d'),
                ];
            }
        }

        return $atRisk;
    }

    /**
     * Индекс баланса (0-100)
     */
    private function calculateBalanceIndex(Collection $spheres, $tasks, string $sphereField): int
    {
        $numSpheres = $spheres->count();
        if ($numSpheres === 0) return 0;

        // Подсчёт задач по сферам
        $tasksBySphere = [];
        if ($tasks instanceof Collection) {
            $tasksBySphere = $tasks->whereNotNull($sphereField)
                ->groupBy($sphereField)
                ->map->count()
                ->toArray();
        }

        $total = array_sum($tasksBySphere);
        if ($total === 0) return 0;

        // Проценты по сферам
        $percentages = [];
        foreach ($spheres as $sphere) {
            $percentages[] = (($tasksBySphere[$sphere->id] ?? 0) / $total) * 100;
        }

        // Идеальное распределение
        $ideal = 100 / $numSpheres;

        // Стандартное отклонение
        $sumSquares = 0;
        foreach ($percentages as $pct) {
            $sumSquares += pow($pct - $ideal, 2);
        }
        $deviation = sqrt($sumSquares / $numSpheres);

        // Максимальное возможное отклонение (всё в одной сфере)
        $maxDeviation = sqrt((pow(100 - $ideal, 2) + ($numSpheres - 1) * pow($ideal, 2)) / $numSpheres);

        if ($maxDeviation === 0.0) return 100;

        return max(0, (int) round(100 * (1 - $deviation / $maxDeviation)));
    }

    /**
     * Тренд баланса по месяцам
     */
    private function getMonthlyBalanceTrend(int $workspaceId, Collection $spheres, int $months): array
    {
        $trend = [];
        $monthNames = [
            1 => 'Янв', 2 => 'Фев', 3 => 'Мар', 4 => 'Апр',
            5 => 'Май', 6 => 'Июн', 7 => 'Июл', 8 => 'Авг',
            9 => 'Сен', 10 => 'Окт', 11 => 'Ноя', 12 => 'Дек',
        ];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $start = $date->copy()->startOfMonth()->toDateString();
            $end = $date->copy()->endOfMonth()->toDateString();

            $tasks = $this->getTasksWithSpheres($workspaceId)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('tasks.due_date', [$start, $end])
                        ->orWhereBetween('tasks.completed_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
                })
                ->get();

            $trend[] = [
                'month' => $monthNames[$date->month] . ' ' . $date->year,
                'month_key' => $date->format('Y-m'),
                'balance_index' => $this->calculateBalanceIndex($spheres, $tasks, 'resolved_sphere_id'),
                'is_current' => $i === 0,
            ];
        }

        return $trend;
    }

    /**
     * Годовые тренды по сферам
     */
    private function getSphereYearlyTrends(int $workspaceId, Collection $spheres): array
    {
        // Сравниваем последние 3 месяца с предыдущими 3
        $recentStart = Carbon::now()->subMonths(3)->startOfMonth()->toDateString();
        $recentEnd = Carbon::now()->endOfMonth()->toDateString();
        $olderStart = Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
        $olderEnd = Carbon::now()->subMonths(3)->startOfMonth()->subDay()->toDateString();

        $recentTasks = $this->getTasksWithSpheres($workspaceId)
            ->where(function ($q) use ($recentStart, $recentEnd) {
                $q->whereBetween('tasks.due_date', [$recentStart, $recentEnd])
                    ->orWhereBetween('tasks.completed_at', [$recentStart . ' 00:00:00', $recentEnd . ' 23:59:59']);
            })
            ->get();

        $olderTasks = $this->getTasksWithSpheres($workspaceId)
            ->where(function ($q) use ($olderStart, $olderEnd) {
                $q->whereBetween('tasks.due_date', [$olderStart, $olderEnd])
                    ->orWhereBetween('tasks.completed_at', [$olderStart . ' 00:00:00', $olderEnd . ' 23:59:59']);
            })
            ->get();

        $recentTotal = max($recentTasks->whereNotNull('resolved_sphere_id')->count(), 1);
        $olderTotal = max($olderTasks->whereNotNull('resolved_sphere_id')->count(), 1);

        $trends = [];
        foreach ($spheres as $sphere) {
            $recentPct = round(($recentTasks->where('resolved_sphere_id', $sphere->id)->count() / $recentTotal) * 100);
            $olderPct = round(($olderTasks->where('resolved_sphere_id', $sphere->id)->count() / $olderTotal) * 100);
            $diff = $recentPct - $olderPct;

            $trend = 'stable';
            if ($recentPct == 0 && $olderPct == 0) {
                $trend = 'stalled';
            } elseif ($diff > 3) {
                $trend = 'growing';
            } elseif ($diff < -3) {
                $trend = 'falling';
            }

            $trends[] = [
                'sphere_id' => $sphere->id,
                'name' => $sphere->name,
                'color' => $sphere->color,
                'trend' => $trend,
                'recent_pct' => $recentPct,
                'older_pct' => $olderPct,
                'avg_attention' => (int) round(($recentPct + $olderPct) / 2),
            ];
        }

        return $trends;
    }

    /**
     * Получить цели с прогрессом
     */
    private function getGoalsWithProgress(int $workspaceId): array
    {
        $goals = Goal::where('workspace_id', $workspaceId)
            ->with('lifeSphere:id,name,color')
            ->get();

        if ($goals->isEmpty()) {
            return [];
        }

        return $goals->map(function ($goal) {
            $progress = $goal->progress;

            return [
                'id' => $goal->id,
                'name' => $goal->name,
                'color' => $goal->color ?? $goal->lifeSphere?->color ?? '#9ca3af',
                'sphere_name' => $goal->lifeSphere?->name,
                'sphere_color' => $goal->lifeSphere?->color,
                'progress' => $progress,
                'status' => $goal->status,
            ];
        })->toArray();
    }

    /**
     * Пустой ответ когда нет сфер
     */
    private function emptyResponse(string $period): array
    {
        return [
            'period' => $period,
            'spheres' => [],
            'missing_spheres' => [],
            'goals' => [],
            'summary' => [
                'total_tasks' => 0,
                'completed' => 0,
                'spheres_active' => 0,
                'spheres_total' => 0,
                'completion_rate' => 0,
            ],
            'balance_index' => 0,
        ];
    }
}
