<?php

namespace Modules\UserGeneratorRunner\Services;

use Exception;
use Modules\Common\Dtos\RandomUserItem;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\UserGeneratorRunner\Events\UserGeneratedEvent;
use Modules\UserGeneratorRunner\Events\UserGeneratedQueueableEvent;

class GenerateUsersService implements TaskServiceInterface
{
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly RandomUserService $service) { }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $this->info("Generating users...\n");

        $users = $this->service->setToScreen($this->toScreen)
            ->execute();

        $users->map(function (RandomUserItem $user) {
            if ($this->queueable) {
                $this->line('Dispatching UserGeneratedQueueableEvent');

                UserGeneratedQueueableEvent::dispatch($user);
            }

            $this->line('Dispatching UserGeneratedEvent event.');

            UserGeneratedEvent::dispatch($user, $this->toScreen);
        });
    }
}
