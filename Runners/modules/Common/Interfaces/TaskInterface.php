<?php

namespace Modules\Common\Interfaces;

use Modules\Common\Enum\TaskRunnerSchedule;

interface TaskInterface
{
    public function execute(): void;

    public function runSchedule(): TaskRunnerSchedule;

    public function setToScreen(bool $toScreen): self;

    public function setQueueable(bool $dispatch): self;
}
