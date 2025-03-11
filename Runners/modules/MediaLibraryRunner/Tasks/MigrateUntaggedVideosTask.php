<?php

namespace Modules\MediaLibraryRunner\Tasks;

use Modules\Common\Enum\TaskRunnerSchedule;
use Modules\Common\Interfaces\TaskInterface;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Jobs\MigrateUntaggedVideosJob;
use Modules\MediaLibraryRunner\Services\MigrateUntaggedVideosService;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateUntaggedVideosTask implements TaskInterface
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly MigrateUntaggedVideosService $service)
    {
    }

    public function execute(): void
    {
        if (!config("$this->UNTAGGED_VIDEOS.task_enabled")) {
            $this->warning('The MigrateUntaggedVideosTask is disabled.');

            return;
        }

        if ($this->queueable) {
            $this->line('Sending request to MigrateUntaggedVideosJob');

            MigrateUntaggedVideosJob::dispatch($this->queueable)
                ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
                ->delay(now()->addSecond());

            return;
        }

        $this->line('Running MigrateUntaggedVideosService');

        $this->service->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute();
    }

    public function runSchedule(): TaskRunnerSchedule
    {
        return TaskRunnerSchedule::EVERY_TWO_HOURS;
    }
}
