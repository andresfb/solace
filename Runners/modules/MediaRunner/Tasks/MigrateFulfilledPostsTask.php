<?php

namespace Modules\MediaRunner\Tasks;

use App\Interfaces\TaskInterface;
use Modules\MediaRunner\Jobs\MigrateFulfilledPostsJob;
use Modules\MediaRunner\Services\MigrateFulfilledPostsService;

class MigrateFulfilledPostsTask implements TaskInterface
{
    private bool $toScreen = false;

    private bool $dispatch = true;

    public function __construct(private readonly MigrateFulfilledPostsService $service)
    {
    }

    public function execute(): void
    {
        if ($this->dispatch) {
            if ($this->toScreen) {
                echo "Sending request to MigrateFulfilledPostsJob\n";
            }

            // TODO: set up the queues and send this to it with a delay
            MigrateFulfilledPostsJob::dispatch($this->dispatch);

            return;
        }

        if ($this->toScreen) {
            echo "Running MigrateFulfilledPostsService\n";
        }

        $this->service->setDispatch($this->dispatch)
            ->execute();
    }

    public function setDispatch(bool $dispatch): self
    {
        $this->dispatch = $dispatch;
        return $this;
    }

    public function setToScreen(bool $toScreen): self
    {
        $this->toScreen = $toScreen;
        return $this;
    }
}
