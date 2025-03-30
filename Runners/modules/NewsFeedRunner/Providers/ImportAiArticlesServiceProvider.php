<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\NewsFeedRunner\Tasks\ImportAiArticlesTask;

class ImportAiArticlesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(ImportAiArticlesTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/ai-article-importer.php', 'ai-article-importer');
    }
}
