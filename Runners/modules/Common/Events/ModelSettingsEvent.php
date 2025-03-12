<?php

namespace Modules\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Dtos\ModuleSettingsInfo;

class ModelSettingsEvent
{
    use Dispatchable;

    /**
     * @var callable
     */
    public $callback;

    public function __construct(public ModuleSettingsInfo $modelSettings, callable $callback)
    {
        $this->callback = $callback;
    }
}
