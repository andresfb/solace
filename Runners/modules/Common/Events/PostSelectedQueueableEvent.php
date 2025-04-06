<?php

declare(strict_types=1);

namespace Modules\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Dtos\PostItem;

class PostSelectedQueueableEvent
{
    use Dispatchable;

    public function __construct(public readonly PostItem $postItem) {}
}
