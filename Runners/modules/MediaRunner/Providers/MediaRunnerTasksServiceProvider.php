<?php

namespace Modules\MediaRunner\Providers;

use App\Jobs\ProcessPostJob;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\MediaRunner\Jobs\MigrateFulfilledPostsJob;
use Modules\MediaRunner\Tasks\MigrateFulfilledPostsTask;

class MediaRunnerTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('tasks', function ($app) {
            return collect();
        });

        $this->app->resolving('tasks', function (Collection $tasks) {
            $tasks->push(MigrateFulfilledPostsTask::class);
//            $tasks->push(MigrateFulfilledPostsTask::class);
//            $tasks->push(MigrateFulfilledPostsTask::class);
        });

        $this->app->bind(MigrateFulfilledPostsJob::class);
        $this->app->bind(ProcessPostJob::class);
    }
}
