<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Modules\MediaLibraryRunner\Interfaces\ContentSourceInterface;
use Modules\MediaLibraryRunner\Repositories\ContentSourceJokes;

class MediaLibraryRunnerContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @param Collection<ContentSourceInterface> $contents */
        $this->app->resolving('contents', function (Collection $contents): void {
            $contents->push(ContentSourceJokes::class);
        });
    }
}
