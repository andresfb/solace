<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\MediaLibraryRunner\Services\MigrateUntaggedVideosService;

class MigrateUntaggedVideosJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly bool $queueable) {}

    /**
     * @throws Exception
     */
    public function handle(MigrateUntaggedVideosService $service): void
    {
        $service->setQueueable($this->queueable)
            ->execute();
    }
}
