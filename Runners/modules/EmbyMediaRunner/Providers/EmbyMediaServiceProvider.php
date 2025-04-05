<?php

namespace Modules\EmbyMediaRunner\Providers;

use Illuminate\Support\ServiceProvider;

class EmbyMediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'emby_media_runner');
    }
}
