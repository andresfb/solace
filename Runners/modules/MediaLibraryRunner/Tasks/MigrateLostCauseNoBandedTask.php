<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\MediaLibraryRunner\Services\MigrateLostCauseNoBandedService;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateLostCauseNoBandedTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        MigrateLostCauseNoBandedService $taskTaskService,
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
        return $this->LO_NO_BANDED;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to MigrateFulfilledPostsJob');

        MigrateFulfilledPostsJob::dispatch($this->queueable)
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
