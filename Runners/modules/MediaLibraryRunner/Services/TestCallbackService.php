<?php

namespace Modules\MediaLibraryRunner\Services;

use Modules\Common\Dtos\ModelSettings;
use Modules\MediaLibraryRunner\Events\TestCallbackEvent;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class TestCallbackService
{
    use ModuleConstants;

    public function execute(): void
    {
        $response = null;

        $modelSettings = ModelSettings::from([
            'moduleName' => $this->MODULE_NAME,
            'taskName' => $this->MIGRATE_FULFILLED,
            'settingName' => 'empty_runs_count',
            'response' => [],
        ]);

        TestCallbackEvent::dispatch(
            $modelSettings,
            static function (ModelSettings $settings) use (&$response) {
                $response = $settings;
            }
        );

        dump("From listener:", $response);
    }
}
