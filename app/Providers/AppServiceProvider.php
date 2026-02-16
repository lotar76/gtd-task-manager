<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Goal;
use App\Models\LifeSphere;
use App\Models\Project;
use App\Models\Task;
use App\Observers\GoalObserver;
use App\Observers\LifeSphereObserver;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Регистрация Observers для автоматической инвалидации AI кэша
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);
        Goal::observe(GoalObserver::class);
        LifeSphere::observe(LifeSphereObserver::class);
    }
}

