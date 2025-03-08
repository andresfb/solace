<?php

namespace Modules\UserGeneratorRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\UserGeneratorRunner\Tasks\GenerateUsersTask;

class UserGeneratorRunnerTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('tasks', fn ($app) => collect());

        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(GenerateUsersTask::class);
        });
    }
}
