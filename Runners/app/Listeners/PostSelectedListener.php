<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\ProcessPostService;
use Exception;
use Modules\Common\Events\PostSelectedEvent;
use Throwable;

readonly class PostSelectedListener
{
    public function __construct(private ProcessPostService $service) {}

    /**
     * @throws Exception|Throwable
     */
    public function handle(PostSelectedEvent $event): void
    {
        $this->service->setToScreen($event->toScreen)
            ->execute($event->postItem);
    }
}
