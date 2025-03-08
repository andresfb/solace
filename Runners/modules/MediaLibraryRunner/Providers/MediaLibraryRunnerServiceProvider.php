<?php

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\MediaLibraryRunner\Jobs\MigrateFulfilledPostsJob;

class MediaLibraryRunnerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // We won't need migrations for this. The data already exists in the database.
        // $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // We won't need routes either, this is all backend
        // $this->app->register(RouteServiceProvider::class);

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'media_runner');
        $this->mergeConfigFrom(__DIR__.'/../Config/connection.php', 'database');
        $this->mergeConfigFrom(__DIR__.'/../Config/database.php', 'database.connections.media_runner');

        $this->app->register(MediaLibraryRunnerTasksServiceProvider::class);

        $this->app->bind(MigrateFulfilledPostsJob::class);
    }
}
