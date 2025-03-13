<?php

declare(strict_types=1);

namespace Modules\UserGeneratorRunner\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Dtos\RandomUserItem;

class UserGeneratedQueueableEvent
{
    use Dispatchable;

    public function __construct(public readonly RandomUserItem $user) {}
}
