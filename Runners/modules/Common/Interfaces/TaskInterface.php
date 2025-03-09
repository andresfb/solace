<?php

namespace Modules\Common\Interfaces;

interface TaskInterface
{
    public function execute(): void;

    public function setToScreen(bool $toScreen): self;

    public function setQueueable(bool $dispatch): self;
}
