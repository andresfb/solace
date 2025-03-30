<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Common\Exceptions\EmptyRunException;
use Modules\NewsFeedRunner\Services\ImportPicsumArticlesService;

class ImportPicsumArticlesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @throws EmptyRunException
     */
    public function handle(ImportPicsumArticlesService $service): void
    {
        $service->execute();
    }
}
