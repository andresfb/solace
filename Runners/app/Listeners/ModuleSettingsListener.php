<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\ModuleSettingsService;
use Modules\Common\Events\ModelSettingsEvent;

readonly class ModuleSettingsListener
{
    public function __construct(private ModuleSettingsService $service) {}

    public function handle(ModelSettingsEvent $event): void
    {
        $response = $this->service->getSettings($event->modelSettings);

        call_user_func($event->callback, $response);
    }
}
