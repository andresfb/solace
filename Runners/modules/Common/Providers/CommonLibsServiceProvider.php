<?php

declare(strict_types=1);

namespace Modules\Common\Providers;

use Illuminate\Support\ServiceProvider;

class CommonLibsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
