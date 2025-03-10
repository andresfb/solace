<?php

namespace App\Listeners;

use App\Services\ProcessPostService;
use Illuminate\Support\Facades\Log;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Throwable;

readonly class PostSelectedListener
{
    public function __construct(private ProcessPostService $service) { }

    /**
     * @throws Throwable
     */
    public function handle(PostSelectedEvent $event): void
    {
        try {
            $this->service->setToScreen($event->toScreen)
                ->execute($event->postItem);
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
