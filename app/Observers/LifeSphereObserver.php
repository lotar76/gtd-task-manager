<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AiMirrorCache;
use App\Models\LifeSphere;

class LifeSphereObserver
{
    /**
     * Инвалидация AI кэша для всех периодов при изменении сферы жизни
     */
    private function invalidateAiCache(LifeSphere $lifeSphere): void
    {
        // Сферы жизни влияют на все периоды аналитики
        AiMirrorCache::where('workspace_id', $lifeSphere->workspace_id)
            ->update(['is_stale' => true]);
    }

    public function created(LifeSphere $lifeSphere): void
    {
        $this->invalidateAiCache($lifeSphere);
    }

    public function updated(LifeSphere $lifeSphere): void
    {
        // Инвалидируем при изменении ключевых полей
        if ($lifeSphere->wasChanged(['name', 'color', 'position'])) {
            $this->invalidateAiCache($lifeSphere);
        }
    }

    public function deleted(LifeSphere $lifeSphere): void
    {
        $this->invalidateAiCache($lifeSphere);
    }
}
