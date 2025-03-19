<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Common\Exceptions\EmptyRunException;
use Modules\MediaLibraryRunner\Services\MigrateFulfilledPostsService;

class MigrateFulfilledPostsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly bool $queueable) {}

    /**
     * @throws EmptyRunException
     */
    public function handle(MigrateFulfilledPostsService $service): void
    {
        $service->setQueueable($this->queueable)
            ->execute();
    }
}
