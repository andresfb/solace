<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\ProcessPostService;
use App\Traits\RunnerConstants;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Common\Traits\QueueSelectable;
use Modules\MediaLibraryRunner\Events\PostSelectedQueueableEvent;
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
        try {
            $this->service->execute($event->postItem);
        } catch (Exception|Throwable $e) {
            Log::error($e);

            throw $e;
        }
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
