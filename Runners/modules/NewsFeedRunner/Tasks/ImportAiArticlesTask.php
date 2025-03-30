<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\NewsFeedRunner\Jobs\ImportAiArticlesJob;
use Modules\NewsFeedRunner\Services\ImportAiArticlesService;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImportAiArticlesTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        ImportAiArticlesService $taskTaskService,
        ModuleSettingsService $settingsService
    ) {
        parent::__construct($taskTaskService, $settingsService);
    }

    protected function getModuleName(): string
    {
        return $this->NEWS_FEED;
    }

    protected function getTaskName(): string
    {
        return $this->IMPORT_AI_ARTICLE;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to ImportAiArticlesJob');

        ImportAiArticlesJob::dispatch()
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
