<?php

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\MediaLibraryRunner\Tasks\MigrateFulfilledPostsTask;

class MediaLibraryRunnerTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(MigrateFulfilledPostsTask::class);
            // $tasks->push(MigrateFulfilledPostsTask::class);
            // $tasks->push(MigrateFulfilledPostsTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/migrate-fulfilled.php', 'migrate_fulfilled');
    }
}
