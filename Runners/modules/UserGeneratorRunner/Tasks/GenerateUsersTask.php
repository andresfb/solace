<?php

namespace Modules\UserGeneratorRunner\Tasks;

use Modules\Common\Interfaces\TaskInterface;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\UserGeneratorRunner\Jobs\GenerateUsersJob;
use Modules\UserGeneratorRunner\Services\GenerateUsersService;

class GenerateUsersTask implements TaskInterface
{
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly GenerateUsersService $service) {}

    public function execute(): void
    {
        if (!config('user_generator.generate_users_task_enabled')) {
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

        $this->service->setQueueable($this->queueable)
            ->execute();
    }
}
