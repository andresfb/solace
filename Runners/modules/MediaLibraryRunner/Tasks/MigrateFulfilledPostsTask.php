<?php

namespace Modules\MediaLibraryRunner\Tasks;

use App\Interfaces\TaskInterface;
use App\Traits\Screenable;
use Modules\MediaLibraryRunner\Jobs\MigrateFulfilledPostsJob;
use Modules\MediaLibraryRunner\Services\MigrateFulfilledPostsService;

class MigrateFulfilledPostsTask implements TaskInterface
{
    use Screenable;

    public function __construct(private readonly MigrateFulfilledPostsService $service) {}

    public function execute(): void
    {
        if ($this->dispatch) {
            $this->line('Sending request to MigrateFulfilledPostsJob');

            // TODO: set up the queues and send this to it with a delay
            MigrateFulfilledPostsJob::dispatch($this->dispatch);

            return;
        }

        $this->line('Running MigrateFulfilledPostsService');

        $this->service->setDispatch($this->dispatch)
            ->execute();
    }
}
