<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\NewsFeedRunner\Jobs\ImportImagedArticlesJob;
use Modules\NewsFeedRunner\Services\ImportImagedArticlesService;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImportImagedArticlesTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        ImportImagedArticlesService $taskTaskService,
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
        return $this->IMPORT_IMAGED_ARTICLES;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to ImportImagedArticlesJob');

        ImportImagedArticlesJob::dispatch()
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
