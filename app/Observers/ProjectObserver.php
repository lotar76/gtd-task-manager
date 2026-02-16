<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AiMirrorCache;
use App\Models\Project;

class ProjectObserver
{
    /**
     * Инвалидация AI кэша для всех периодов при изменении проекта
     */
    private function invalidateAiCache(Project $project): void
    {
        // Проекты влияют на все периоды аналитики
        AiMirrorCache::where('workspace_id', $project->workspace_id)
            ->update(['is_stale' => true]);
    }

    public function created(Project $project): void
    {
        $this->invalidateAiCache($project);
    }

    public function updated(Project $project): void
    {
        // Инвалидируем при изменении ключевых полей
        if ($project->wasChanged(['name', 'status', 'goal_id', 'color'])) {
            $this->invalidateAiCache($project);
        }
    }

    public function deleted(Project $project): void
    {
        $this->invalidateAiCache($project);
    }
}
