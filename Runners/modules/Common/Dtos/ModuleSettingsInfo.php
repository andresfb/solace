<?php

namespace Modules\Common\Dtos;

use Spatie\LaravelData\Data;

class ModuleSettingsInfo extends Data
{
    public function __construct(
        public string $moduleName,
        public string $taskName,
        public array $settingNames,
        public array $response,
        public string $action = 'update',
    ) {
        if ($this->settingNames === []) {
            throw new \RuntimeException('Setting names must have at least one name');
        }
    }
}
