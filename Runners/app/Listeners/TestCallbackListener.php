<?php

namespace App\Listeners;

use Modules\MediaLibraryRunner\Events\TestCallbackEvent;

class TestCallbackListener
{
    public function handle(TestCallbackEvent $event): void
    {
        $event->modelSettings->response[$event->modelSettings->settingName] = 20;

        dump($event->modelSettings);

        call_user_func($event->callback, $event->modelSettings);
    }
}
