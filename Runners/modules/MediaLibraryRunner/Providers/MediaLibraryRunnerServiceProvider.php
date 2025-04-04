<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\MediaLibraryRunner\Listeners\PostCreatedListener;

class MediaLibraryRunnerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('contents', fn ($app) => collect());

        $this->app->register(MediaLibraryRunnerTasksServiceProvider::class);

        $this->app->register(MediaLibraryRunnerContentServiceProvider::class);
    }

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
        $this->mergeConfigFrom(__DIR__.'/../Config/horizon.php', 'horizon.environments.dell-mox');

        Event::listen(
            ChangeStatusEvent::class,
            [PostCreatedListener::class, 'handle']
        );
    }
}
