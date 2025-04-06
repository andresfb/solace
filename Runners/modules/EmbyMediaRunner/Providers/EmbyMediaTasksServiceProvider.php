<?php

namespace Modules\EmbyMediaRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\EmbyMediaRunner\Tasks\GenerateMoviePostTask;
use Modules\EmbyMediaRunner\Tasks\IndexMediaTask;

class EmbyMediaTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('tasks', function (Collection $tasks): void {
            $tasks->add(IndexMediaTask::class);
            $tasks->add(GenerateMoviePostTask::class);
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/index-media.php', 'index-media');
        $this->mergeConfigFrom(__DIR__.'/../Config/generate-movie-post.php', 'generate-movie-post');
        $this->mergeConfigFrom(__DIR__.'/../Config/trailer-download.php', 'trailer-download');
        $this->mergeConfigFrom(__DIR__.'/../Config/encode-trailer.php', 'encode-trailer');
    }
}
