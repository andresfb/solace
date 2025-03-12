<?php

namespace App\Listeners;

use App\Services\ProcessPostService;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Throwable;

readonly class PostSelectedListener
{
    public function __construct(private ProcessPostService $service) {}

    /**
     * @throws Exception|Throwable
     */
    public function handle(PostSelectedEvent $event): void
    {
        try {
            $this->service->setToScreen($event->toScreen)
                ->execute($event->postItem);
        } catch (Exception|Throwable $e) {
            Log::error($e);
        }
    }
}
