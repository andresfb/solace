<?php

namespace Modules\MediaLibraryRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\MediaLibraryRunner\Jobs\MigrateFulfilledPostsJob;
use Modules\MediaLibraryRunner\Services\MigrateFulfilledPostsService;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateFulfilledPostsTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        MigrateFulfilledPostsService $taskTaskService,
        ModuleSettingsService        $settingsService
    ) {
        parent::__construct($taskTaskService, $settingsService);
    }

    protected function getModuleName(): string
    {
        return $this->MODULE_NAME;
    }

    protected function getTaskName(): string
    {
        return $this->MIGRATE_FULFILLED;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to MigrateFulfilledPostsJob');

        MigrateFulfilledPostsJob::dispatch($this->queueable)
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
