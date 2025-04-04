<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\NewsFeedRunner\Jobs\ImportQuotedArticlesJob;
use Modules\NewsFeedRunner\Services\ImportQuotedArticlesService;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImportQuotedArticlesTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        ImportQuotedArticlesService $taskTaskService,
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
        return $this->IMPORT_QUOTED_ARTICLE;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to ImportQuotedArticlesJob');

        ImportQuotedArticlesJob::dispatch()
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
