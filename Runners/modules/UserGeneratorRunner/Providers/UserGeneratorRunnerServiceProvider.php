<?php

namespace Modules\UserGeneratorRunner\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\UserGeneratorRunner\Jobs\GenerateUsersJob;

class UserGeneratorRunnerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'user_generator');
        $this->mergeConfigFrom(__DIR__.'/../Config/random-user.php', 'random_user');
        $this->mergeConfigFrom(__DIR__.'/../Config/xsgames.php', 'xsgames');

        $this->app->register(UserGeneratorRunnerTasksServiceProvider::class);

        $this->app->bind(GenerateUsersJob::class);
    }
}
