<?php

namespace Modules\UserGeneratorRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\UserGeneratorRunner\Tasks\GenerateUsersTask;

class UserGeneratorRunnerTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(GenerateUsersTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/generate-users.php', 'generate_users');
    }
}
