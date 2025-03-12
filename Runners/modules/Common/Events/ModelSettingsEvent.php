<?php

namespace Modules\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Dtos\ModelSettings;

class ModelSettingsEvent
{
    use Dispatchable;

    public $callback;

    public function __construct(public ModelSettings $modelSettings, callable $callback)
    {
        $this->callback = $callback;
    }
}
