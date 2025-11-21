<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Comment;
use App\Models\Goal;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Policies\CommentPolicy;
use App\Policies\GoalPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\WorkspacePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Workspace::class => WorkspacePolicy::class,
        Task::class => TaskPolicy::class,
        Project::class => ProjectPolicy::class,
        Goal::class => GoalPolicy::class,
        Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}

