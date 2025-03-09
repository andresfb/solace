<?php

namespace Modules\MediaLibraryRunner\Tasks;

use Modules\Common\Enum\TaskRunnerSchedule;
use Modules\Common\Interfaces\TaskInterface;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Jobs\MigrateFulfilledPostsJob;
use Modules\MediaLibraryRunner\Services\MigrateFulfilledPostsService;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateFulfilledPostsTask implements TaskInterface
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly MigrateFulfilledPostsService $service) {}

    public function execute(): void
    {
        if (!config("$this->MIGRATE_FULFILLED.task_enabled")) {
            $this->warning('The GenerateUsersTask is disabled.');

            return;
        }

        if ($this->queueable) {
            $this->line('Sending request to MigrateFulfilledPostsJob');

            MigrateFulfilledPostsJob::dispatch($this->queueable)
                ->onQueue(config("$this->MIGRATE_FULFILLED.horizon_queue"))
                ->delay(now()->addSecond());

            return;
        }

        $this->line('Running MigrateFulfilledPostsService');

        $this->service->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute();
    }

    public function runSchedule(): TaskRunnerSchedule
    {
        return TaskRunnerSchedule::EVERY_TWO_HOURS;
    }
}
