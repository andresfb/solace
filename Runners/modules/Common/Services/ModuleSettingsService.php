<?php

namespace Modules\Common\Services;

use Modules\Common\Dtos\ModelSettings;
use Modules\Common\Events\ModelSettingsEvent;

class ModuleSettingsService
{
    public function getSetting(ModelSettings $modelSettings): ModelSettings
    {
        $response = null;

        ModelSettingsEvent::dispatch(
            $modelSettings,
            static function (ModelSettings $settings) use (&$response) {
                $response = $settings;
            }
        );

        if ($response === null) {
            throw new \RuntimeException('Model settings not found');
        }

        return $response;
    }
}
