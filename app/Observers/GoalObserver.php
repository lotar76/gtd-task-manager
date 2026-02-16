<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AiMirrorCache;
use App\Models\Goal;

class GoalObserver
{
    /**
     * Инвалидация AI кэша для всех периодов при изменении цели
     */
    private function invalidateAiCache(Goal $goal): void
    {
        // Цели влияют на все периоды аналитики
        AiMirrorCache::where('workspace_id', $goal->workspace_id)
            ->update(['is_stale' => true]);
    }

    public function created(Goal $goal): void
    {
        $this->invalidateAiCache($goal);
    }

    public function updated(Goal $goal): void
    {
        // Инвалидируем при изменении ключевых полей
        if ($goal->wasChanged(['name', 'status', 'life_sphere_id', 'deadline', 'color'])) {
            $this->invalidateAiCache($goal);
        }
    }

    public function deleted(Goal $goal): void
    {
        $this->invalidateAiCache($goal);
    }
}
