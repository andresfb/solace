<?php

namespace App\Listeners;

use App\Services\ModuleSettingsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Common\Events\UpdateModelSettingsEvent;

readonly class UpdateModelSettingsListener implements ShouldQueue
{
    public function __construct(private ModuleSettingsService $settingsService) {}

    public function handle(UpdateModelSettingsEvent $event): void
    {
        $this->settingsService->updateSetting($event->modelSettings);
    }

    public function viaConnection(): string
    {
        return config('queue.default');
    }

    public function viaQueue(): string
    {
        return config('horizon.default_queue');
    }

    /**
     * Get the number of seconds before the job should be processed.
     */
    public function withDelay(): int
    {
        return 2;
    }
}
