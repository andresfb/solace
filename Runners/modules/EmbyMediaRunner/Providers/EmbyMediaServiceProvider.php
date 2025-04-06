<?php

namespace Modules\EmbyMediaRunner\Providers;

use Illuminate\Support\ServiceProvider;

class EmbyMediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(EmbyMediaTasksServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'emby_media_runner');
        $this->mergeConfigFrom(__DIR__.'/../Config/emby-api.php', 'emby-api');
        $this->mergeConfigFrom(__DIR__.'/../Config/meilisearch.php', 'meilisearch');
        $this->mergeConfigFrom(__DIR__.'/../Config/redis.php', 'database.redis');
        $this->mergeConfigFrom(__DIR__.'/../Config/queue.php', 'queue.connections');
        $this->mergeConfigFrom(__DIR__.'/../Config/horizon.php', 'horizon.environments.tiger-mox');
    }
}
