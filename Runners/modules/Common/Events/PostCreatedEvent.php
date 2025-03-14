<?php

declare(strict_types=1);

namespace Modules\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Enum\RunnerStatus;

class PostCreatedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly string $origin,
        public readonly int $libraryPostId,
        public readonly RunnerStatus $runnerStatus
    ) {}
}
