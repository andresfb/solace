<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\MediaLibraryRunner\Interfaces\ContentSourceInterface;
use Modules\MediaLibraryRunner\Repositories\ContentSourceJokes;
use Modules\MediaLibraryRunner\Repositories\ContentSourceQuotes;
use Modules\MediaLibraryRunner\Repositories\ContentSourceWords;

class MediaLibraryRunnerContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @param Collection<ContentSourceInterface> $contents */
        $this->app->resolving('contents', function (Collection $contents): void {
            $contents->push(ContentSourceJokes::class);
            $contents->push(ContentSourceQuotes::class);
            $contents->push(ContentSourceWords::class);
        });
    }
}
