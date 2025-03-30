<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\NewsFeedRunner\Jobs\ImportPicsumArticlesJob;
use Modules\NewsFeedRunner\Services\ImportPicsumArticlesService;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class ImportPicsumArticlesTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        ImportPicsumArticlesService $taskTaskService,
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
        return $this->IMPORT_PICSUM_ARTICLE;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to ImportPicsumArticlesJob');

        ImportPicsumArticlesJob::dispatch()
            ->onQueue(config("$this->MODULE_NAME.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
