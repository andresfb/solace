<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\ProcessPostService;
use App\Traits\RunnerConstants;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Events\PostSelectedQueueableEvent;
use Modules\Common\Traits\QueueSelectable;
use Throwable;

class PostSelectedQueueableListener implements ShouldQueue
{
    use QueueSelectable;
    use RunnerConstants;

    public function __construct(private readonly ProcessPostService $service) {}

    /**
     * @throws Exception|Throwable
     */
    public function handle(PostSelectedQueueableEvent $event): void
    {
        $this->service->execute($event->postItem);
    }

    public function viaConnection(): string
    {
        return $this->getConnection($this->POSTS);
    }

    public function viaQueue(): string
    {
        return $this->getQueue($this->POSTS);
    }

    /**
     * Get the number of seconds before the job should be processed.
     */
    public function withDelay(): int
    {
        return 1;
    }
}
