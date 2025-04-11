<?php

namespace Modules\EmbyMediaRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\EmbyMediaRunner\Jobs\GenerateSeriesPostJob;
use Modules\EmbyMediaRunner\Services\GenerateSeriesPostService;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;

class GenerateSeriesPostTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        GenerateSeriesPostService $taskTaskService,
        ModuleSettingsService $settingsService
    ) {
        parent::__construct($taskTaskService, $settingsService);
    }

    protected function getModuleName(): string
    {
        return $this->MODULE_NAME;
    }

    protected function getTaskName(): string
    {
        return $this->GENERATE_SERIES_POST;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to GenerateSeriesPostJob');

        GenerateSeriesPostJob::dispatch()
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
