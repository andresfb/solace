<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class PostCreatedListener implements ShouldQueue
{
    use ModuleConstants;

    public function handle(ChangeStatusEvent $event): void
    {
        LibraryPost::where('id', $event->libraryPostId)
            ->update([
                'runner_status' => $event->runnerStatus,
            ]);
    }

    public function shouldQueue(ChangeStatusEvent $event): bool
    {
        return $event->origin === $this->MEDIA_LIBRARY;
    }

    public function viaQueue(): string
    {
        return config("$this->MODULE_NAME.horizon_queue");
    }

    public function withDelay(): int
    {
        return 2; // Seconds
    }
}
