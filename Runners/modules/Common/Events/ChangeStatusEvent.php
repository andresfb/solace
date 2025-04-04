<?php

declare(strict_types=1);

namespace Modules\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Common\Enum\RunnerStatus;

class ChangeStatusEvent
{
    use Dispatchable;

    public function __construct(
        public readonly string $origin,
        public readonly int $modelId,
        public readonly RunnerStatus $runnerStatus,
        public readonly string $source = '',
    ) {}
}
