<?php

namespace Modules\UserGeneratorRunner\Tasks;

use Exception;
use Modules\Common\Enum\TaskRunnerSchedule;
use Modules\Common\Interfaces\TaskInterface;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\UserGeneratorRunner\Jobs\GenerateUsersJob;
use Modules\UserGeneratorRunner\Services\GenerateUsersService;
use Modules\UserGeneratorRunner\Traits\ModuleConstants;

class GenerateUsersTask implements TaskInterface
{
    use Screenable;
    use SendToQueue;
    use ModuleConstants;

    public function __construct(private readonly GenerateUsersService $service) {}

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        if (!config("$this->GENERATE_USERS.task_enabled")) {
            $this->warning('The GenerateUsersTask is disabled.');

            return;
        }

        if ($this->queueable) {
            $this->line('Sending request to GenerateUsersJob');

            GenerateUsersJob::dispatch($this->queueable)
                ->onQueue(config("$this->GENERATE_USERS.horizon_queue"))
                ->delay(now()->addSecond());

            return;
        }

        $this->service->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute();
    }

    public function runSchedule(): TaskRunnerSchedule
    {
        return TaskRunnerSchedule::HOURLY;
    }
}
