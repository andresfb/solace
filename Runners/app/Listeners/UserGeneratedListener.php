<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\RegisterUserService;
use Illuminate\Support\Facades\Log;
use Modules\UserGeneratorRunner\Events\UserGeneratedEvent;
use Throwable;

readonly class UserGeneratedListener
{
    public function __construct(private RegisterUserService $service) {}

    public function handle(UserGeneratedEvent $event): void
    {
        try {
            $this->service->setToScreen($event->toScreen)
                ->execute($event->user);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
