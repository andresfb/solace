<?php

namespace App\Listeners;

use App\Services\ProcessPostService;
use App\Traits\RunnerConstants;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Common\Traits\QueueSelectable;
use Modules\MediaLibraryRunner\Events\PostSelectedQueueableEvent;
use Throwable;

class PostSelectedQueueableListener implements ShouldQueue
{
    use QueueSelectable;
    use RunnerConstants;

    public function __construct(private readonly ProcessPostService $postService)
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(PostSelectedQueueableEvent $event): void
    {
        try {
            $this->postService->execute($event->postItem);
        } catch (Throwable $e) {
            Log::error($e->getMessage());

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
