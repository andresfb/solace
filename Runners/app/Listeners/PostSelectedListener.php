<?php

namespace App\Listeners;

use App\Services\ProcessPostService;
use Illuminate\Support\Facades\Log;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Throwable;

readonly class PostSelectedListener
{
    public function __construct(private ProcessPostService $postService) { }

    /**
     * @throws Throwable
     */
    public function handle(PostSelectedEvent $event): void
    {
        try {
            $this->postService->execute($event->postItem);
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            throw $e;
        }
    }
}
