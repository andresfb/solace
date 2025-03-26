<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\NewsFeedRunner\Tasks\ImportImagedArticlesTask;

class ImportImagedArticlesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(ImportImagedArticlesTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/imaged-article-importer.php', 'imaged-article-importer');
    }
}
