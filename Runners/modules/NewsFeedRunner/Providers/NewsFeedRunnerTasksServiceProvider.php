<?php

namespace Modules\NewsFeedRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\NewsFeedRunner\Tasks\ImportAiArticlesTask;
use Modules\NewsFeedRunner\Tasks\ImportImagedArticlesTask;
use Modules\NewsFeedRunner\Tasks\ImportPicsumArticlesTask;

class NewsFeedRunnerTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->push(ImportImagedArticlesTask::class);
            $tasks->push(ImportPicsumArticlesTask::class);
            $tasks->push(ImportAiArticlesTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/imaged-article-importer.php', 'imaged-article-importer');
        $this->mergeConfigFrom(__DIR__.'/../Config/picsum-article-importer.php', 'picsum-article-importer');
        $this->mergeConfigFrom(__DIR__.'/../Config/ai-article-importer.php', 'ai-article-importer');
    }
}
