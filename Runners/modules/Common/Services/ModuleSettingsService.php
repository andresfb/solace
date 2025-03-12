<?php

namespace Modules\Common\Services;

use Modules\Common\Dtos\ModuleSettingsInfo;
use Modules\Common\Events\ModelSettingsEvent;
use Modules\Common\Events\UpdateModelSettingsEvent;

class ModuleSettingsService
{
    public function getSetting(ModuleSettingsInfo $modelSettings): ModuleSettingsInfo
    {
        $response = null;

        ModelSettingsEvent::dispatch(
            $modelSettings,
            static function (ModuleSettingsInfo $settings) use (&$response): void {
                $response = $settings;
            }
        );

        if (!$response instanceof \Modules\Common\Dtos\ModuleSettingsInfo) {
            throw new \RuntimeException('Model settings not found');
        }

        return $response;
    }

    public function disableTask(ModuleSettingsInfo $info): void
    {
        $info->action = 'update';
        $info->response['is_enabled'] = "0";

        UpdateModelSettingsEvent::dispatch($info);
    }
}
