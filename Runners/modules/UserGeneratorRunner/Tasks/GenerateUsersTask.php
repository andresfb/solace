<?php

namespace Modules\UserGeneratorRunner\Tasks;

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

    public function execute(): void
    {
        if (!config("$this->GENERATE_USERS.task_enabled")) {
            $this->warning('The GenerateUsersTask is disabled.');

            return;
        }

        if ($this->queueable) {
            $this->line('Sending request to GenerateUsersJob');

            // TODO: set up the queues and send this to it with a delay
            GenerateUsersJob::dispatch($this->queueable);

            return;
        }

        $this->line('Running GenerateUsersService');

        $this->service->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute();
    }
}
