<?php

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Common\Events\PostCreatedEvent;
use Modules\MediaLibraryRunner\Jobs\CreatePostItemJob;
use Modules\MediaLibraryRunner\Jobs\MigrateFulfilledPostsJob;
use Modules\MediaLibraryRunner\Jobs\MigrateUntaggedVideosJob;
use Modules\MediaLibraryRunner\Listeners\PostCreatedListener;

class MediaLibraryRunnerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // We won't need routes either, this is all backend
        // $this->app->register(RouteServiceProvider::class);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'media_runner');
        $this->mergeConfigFrom(__DIR__.'/../Config/connection.php', 'database');
        $this->mergeConfigFrom(__DIR__.'/../Config/database.php', 'database.connections.media_runner');
        $this->mergeConfigFrom(__DIR__.'/../Config/redis.php', 'database.redis');
        $this->mergeConfigFrom(__DIR__.'/../Config/queue.php', 'queue.connections');
        $this->mergeConfigFrom(__DIR__.'/../Config/ollama-laravel.php', 'ollama-laravel');

        $this->app->register(MediaLibraryRunnerTasksServiceProvider::class);

        $this->app->bind(CreatePostItemJob::class);
        $this->app->bind(MigrateFulfilledPostsJob::class);
        $this->app->bind(MigrateUntaggedVideosJob::class);

        Event::listen(
            PostCreatedEvent::class,
            [PostCreatedListener::class, 'handle']
        );
    }
}
