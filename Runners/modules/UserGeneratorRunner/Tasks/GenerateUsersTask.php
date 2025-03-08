<?php

namespace Modules\UserGeneratorRunner\Tasks;

use App\Interfaces\TaskInterface;
use App\Traits\Screenable;
use Modules\UserGeneratorRunner\Jobs\GenerateUsersJob;
use Modules\UserGeneratorRunner\Services\GenerateUsersService;

class GenerateUsersTask implements TaskInterface
{
    use Screenable;

    public function __construct(private readonly GenerateUsersService $service) {}

    public function execute(): void
    {
        if ($this->dispatch) {
            $this->line('Sending request to GenerateUsersJob');

            // TODO: set up the queues and send this to it with a delay
            GenerateUsersJob::dispatch($this->dispatch);

            return;
        }

        $this->line('Running GenerateUsersService');

        $this->service->setDispatch($this->dispatch)
            ->execute();
    }
}
