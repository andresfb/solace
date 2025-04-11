<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\EmbyMediaRunner\Jobs\GenerateMoviePostJob;
use Modules\EmbyMediaRunner\Services\GenerateMoviePostService;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;

final class GenerateMoviePostTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        GenerateMoviePostService $taskTaskService,
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
        return $this->GENERATE_MOVIE_POST;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to GenerateMoviePostJob');

        GenerateMoviePostJob::dispatch()
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
