<?php

namespace Modules\Common\Exceptions;

use Exception;
use Modules\Common\Dtos\ModuleSettingsInfo;
use Throwable;

class EmptyRunException extends Exception
{
    public ModuleSettingsInfo $modelSettings;

    public function __construct(string $moduleName, string $taskName, string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 1, $previous);

        $this->modelSettings = ModuleSettingsInfo::from([
            'moduleName' => $moduleName,
            'taskName' => $taskName,
            'settingNames' => 'empty_runs_count',
            'response' => [],
            'action' => 'increment'
        ]);
    }
}
