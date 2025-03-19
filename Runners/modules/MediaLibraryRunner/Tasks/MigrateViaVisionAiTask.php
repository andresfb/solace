<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\MediaLibraryRunner\Jobs\MigrateViaVisionAiJob;
use Modules\MediaLibraryRunner\Services\MigrateViaVisionAiService;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateViaVisionAiTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        MigrateViaVisionAiService $taskTaskService,
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
        return $this->POST_VISION_AI;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to MigrateViaAiJob');

        MigrateViaVisionAiJob::dispatch($this->queueable)
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
