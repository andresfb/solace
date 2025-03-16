<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\MediaLibraryRunner\Services\MigrateViaChatAiService;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateViaChatAiTask extends BaseTask
{
    use ModuleConstants;

    public function __construct(
        MigrateViaChatAiService $taskTaskService,
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
        return $this->POST_CHAT_AI;
    }

    protected function dispatchEvent(): void
    {
        // TODO: Implement dispatchEvent() method.
    }
}
