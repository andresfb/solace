<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Dtos\PostUpdateItem;

class UpdatePostQueueableEvent
{
    use Dispatchable;

    public function __construct(public PostUpdateItem $postUpdateItem) {}
}
