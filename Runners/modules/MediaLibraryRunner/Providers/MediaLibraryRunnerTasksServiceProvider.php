<?php

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\MediaLibraryRunner\Tasks\MigrateFulfilledPostsTask;
use Modules\MediaLibraryRunner\Tasks\MigrateUntaggedVideosTask;
use Modules\MediaLibraryRunner\Tasks\MigrateViaAiTask;

class MediaLibraryRunnerTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(MigrateFulfilledPostsTask::class);
            $tasks->push(MigrateUntaggedVideosTask::class);
            $tasks->push(MigrateViaAiTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/fulfilled.php', 'fulfilled');
        $this->mergeConfigFrom(__DIR__.'/../Config/untagged-videos.php', 'untagged_videos');
        $this->mergeConfigFrom(__DIR__.'/../Config/post-via-ai.php', 'post-via_ai');
    }
}
