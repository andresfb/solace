<?php

namespace Modules\MediaLibraryRunner\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Dtos\ModelSettings;

class TestCallbackEvent
{
    use Dispatchable;

    public $callback;

    public function __construct(public ModelSettings $modelSettings, callable $callback)
    {
        $this->callback = $callback;
    }
}
