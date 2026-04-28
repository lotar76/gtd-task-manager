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
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Yandex\YandexExtendSocialite;
use SocialiteProviders\MailRu\MailRuExtendSocialite;

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
        // Socialite providers
        Event::listen(SocialiteWasCalled::class, YandexExtendSocialite::class);
        Event::listen(SocialiteWasCalled::class, MailRuExtendSocialite::class);

        // Регистрация Observers для автоматической инвалидации AI кэша
        Task::observe(TaskObserver::class);
        Project::observe(ProjectObserver::class);
        Goal::observe(GoalObserver::class);
        LifeSphere::observe(LifeSphereObserver::class);
    }
}

