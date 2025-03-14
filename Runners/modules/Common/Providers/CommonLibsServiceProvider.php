<?php

declare(strict_types=1);

namespace Modules\Common\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Modules\Common\Events\ModelSettingsEvent;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\Common\Events\UpdateModelSettingsEvent;
use Modules\Common\Exceptions\Handler;

class CommonLibsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the module's exception handler
        $this->app->singleton(ExceptionHandler::class, fn ($app): Handler => new Handler($app));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->bind(ModelSettingsEvent::class);
        $this->app->bind(UpdateModelSettingsEvent::class);
        $this->app->bind(ChangeStatusEvent::class);
    }
}
