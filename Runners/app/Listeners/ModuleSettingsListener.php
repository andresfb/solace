<?php

namespace App\Listeners;

use App\Models\ModuleSettings\ModuleSetting;
use Modules\Common\Events\ModelSettingsEvent;

class ModuleSettingsListener
{
    public function handle(ModelSettingsEvent $event): void
    {
        $settings = ModuleSetting::select(['name', 'value'])
            ->where('module_name', $event->modelSettings->moduleName)
            ->where('task_name', $event->modelSettings->taskName)
            ->whereIn('name', $event->modelSettings->settingNames)
            ->get();

        if ($settings->isEmpty()) {
            throw new \RuntimeException('No settings found');
        }

        foreach ($settings as $setting) {
            $event->modelSettings->response[$setting->name] = $setting->value;
        }

        call_user_func($event->callback, $event->modelSettings);
    }
}
