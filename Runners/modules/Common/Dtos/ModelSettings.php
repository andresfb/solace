<?php

namespace Modules\Common\Dtos;

use Spatie\LaravelData\Data;

class ModelSettings extends Data
{
    public function __construct(
        public string $moduleName,
        public string $taskName,
        public array $settingNames,
        public array $response,
    ) { }
}
