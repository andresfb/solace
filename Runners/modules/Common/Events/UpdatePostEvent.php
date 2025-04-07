<?php

namespace Modules\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Dtos\PostUpdateItem;

class UpdatePostEvent
{
    use Dispatchable;

    public function __construct(
        public PostUpdateItem $postUpdateItem,
        public readonly bool $toScreen = false
    ) {}
}
