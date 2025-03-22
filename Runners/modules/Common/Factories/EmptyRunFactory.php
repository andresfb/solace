<?php

declare(strict_types=1);

namespace Modules\Common\Factories;

use Modules\Common\Dtos\ModuleSettingsInfo;
use Modules\Common\Events\UpdateModelSettingsEvent;
use Modules\Common\Exceptions\EmptyRunException;

class EmptyRunFactory
{
    /**
     * @throws EmptyRunException
     */
    public static function handler(string $moduleName, string $taskName, string $message): void
    {
        $modelSettings = ModuleSettingsInfo::from([
            'moduleName' => $moduleName,
            'taskName' => $taskName,
            'settingNames' => ['empty_runs_count'],
            'response' => [],
            'action' => 'increment',
        ]);

        UpdateModelSettingsEvent::dispatch($modelSettings);

        throw new EmptyRunException($message);
    }
}
