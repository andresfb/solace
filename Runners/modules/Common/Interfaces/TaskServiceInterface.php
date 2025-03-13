<?php

declare(strict_types=1);

namespace Modules\Common\Interfaces;

interface TaskServiceInterface
{
    public function execute(): void;

    public function setToScreen(bool $toScreen): self;

    public function setQueueable(bool $dispatch): self;
}
