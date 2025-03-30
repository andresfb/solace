<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\NewsFeedRunner\Tasks\ImportPicsumArticlesTask;

class ImportPicsumArticlesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(ImportPicsumArticlesTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/picsum-article-importer.php', 'picsum-article-importer');
    }
}
