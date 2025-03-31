<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\NewsFeedRunner\Listeners\PostCreatedListener;

class NewsFeedRunnerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(NewsFeedRunnerTasksServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'news_feed_runner');
        $this->mergeConfigFrom(__DIR__.'/../Config/connection.php', 'database');
        $this->mergeConfigFrom(__DIR__.'/../Config/database.php', 'database.connections.news_feed_runner');
        $this->mergeConfigFrom(__DIR__.'/../Config/redis.php', 'database.redis');
        $this->mergeConfigFrom(__DIR__.'/../Config/queue.php', 'queue.connections');
        $this->mergeConfigFrom(__DIR__.'/../Config/horizon.php', 'horizon.environments.tiger-mox');

        Event::listen(
            ChangeStatusEvent::class,
            [PostCreatedListener::class, 'handle']
        );
    }
}
