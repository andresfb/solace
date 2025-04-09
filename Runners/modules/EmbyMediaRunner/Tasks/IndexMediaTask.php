<?php

namespace Modules\EmbyMediaRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\EmbyMediaRunner\Jobs\IndexMoviesJob;
use Modules\EmbyMediaRunner\Services\IndexMediaService;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;

final class IndexMediaTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        IndexMediaService $taskTaskService,
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
        return $this->INDEX_MEDIA;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to ImportImagedArticlesJob');

        IndexMoviesJob::dispatch()
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());

        // TODO: dispatch the jobs for the other media (tv shows, collections, Music Videos, Music).
    }
}
