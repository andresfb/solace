<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\RegisterUserService;
use App\Traits\RunnerConstants;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Common\Traits\QueueSelectable;
use Modules\UserGeneratorRunner\Events\UserGeneratedQueueableEvent;
use Throwable;

class UserGeneratedQueueableListener implements ShouldQueue
{
    use QueueSelectable;
    use RunnerConstants;

    public function __construct(private readonly RegisterUserService $service) {}

    /**
     * @throws Throwable
     */
    public function handle(UserGeneratedQueueableEvent $event): void
    {
        try {
            $this->service->execute($event->user);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }
    }

    public function viaConnection(): string
    {
        return $this->getConnection($this->REGISTER_USER);
    }

    public function viaQueue(): string
    {
        return $this->getQueue($this->REGISTER_USER);
    }

    /**
     * Get the number of seconds before the job should be processed.
     */
    public function withDelay(): int
    {
        return 1;
    }
}
