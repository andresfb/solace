<?php

namespace App\Listeners;

use App\Events\UpdatePostQueueableEvent;
use App\Services\UpdatePostService;
use App\Traits\RunnerConstants;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Traits\QueueSelectable;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UpdatePostQueueableListener implements ShouldQueue
{
    use QueueSelectable;
    use RunnerConstants;

    public function __construct(private readonly UpdatePostService $service) {}

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function handle(UpdatePostQueueableEvent $event): void
    {
        $this->service->execute($event->postUpdateItem);
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
