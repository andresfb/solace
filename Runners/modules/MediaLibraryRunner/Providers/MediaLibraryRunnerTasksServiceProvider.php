<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\MediaLibraryRunner\Tasks\MigrateFulfilledPostsTask;
use Modules\MediaLibraryRunner\Tasks\MigrateUntaggedVideosTask;
use Modules\MediaLibraryRunner\Tasks\MigrateViaChatAiTask;
use Modules\MediaLibraryRunner\Tasks\MigrateViaVisionAiTask;

class MediaLibraryRunnerTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(MigrateFulfilledPostsTask::class);
            $tasks->push(MigrateUntaggedVideosTask::class);
            $tasks->push(MigrateViaVisionAiTask::class);
            $tasks->push(MigrateViaChatAiTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/fulfilled.php', 'fulfilled');
        $this->mergeConfigFrom(__DIR__.'/../Config/untagged-videos.php', 'untagged_videos');
        $this->mergeConfigFrom(__DIR__ . '/../Config/post-vision-ai.php', 'post-vision-ai');
        $this->mergeConfigFrom(__DIR__ . '/../Config/post-chat-ai.php', 'post-chat-ai');
    }
}
