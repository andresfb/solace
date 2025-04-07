<?php

namespace App\Listeners;

use App\Services\UpdatePostService;
use Exception;
use Modules\Common\Events\UpdatePostEvent;

readonly class UpdatePostListener
{
    public function __construct(private UpdatePostService $service) {}

    /**
     * @throws Exception
     */
    public function handle(UpdatePostEvent $event): void
    {
        $this->service->setToScreen($event->toScreen)
            ->execute($event->postUpdateItem);
    }
}
