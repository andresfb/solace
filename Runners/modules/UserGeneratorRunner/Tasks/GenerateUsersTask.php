<?php

declare(strict_types=1);

namespace Modules\UserGeneratorRunner\Tasks;

use Modules\Common\Services\ModuleSettingsService;
use Modules\Common\Tasks\BaseTask;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\UserGeneratorRunner\Jobs\GenerateUsersJob;
use Modules\UserGeneratorRunner\Services\GenerateUsersService;
use Modules\UserGeneratorRunner\Traits\ModuleConstants;

class GenerateUsersTask extends BaseTask
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;

    public function __construct(
        GenerateUsersService $taskService,
        ModuleSettingsService $settingsService
    ) {
        parent::__construct($taskService, $settingsService);
    }

    protected function getModuleName(): string
    {
        return $this->MODULE_NAME;
    }

    protected function getTaskName(): string
    {
        return $this->GENERATE_USERS;
    }

    protected function dispatchEvent(): void
    {
        $this->line('Sending request to GenerateUsersJob');

        GenerateUsersJob::dispatch($this->queueable)
            ->onQueue(config("$this->GENERATE_USERS.horizon_queue"))
            ->delay(now()->addSecond());
    }
}
