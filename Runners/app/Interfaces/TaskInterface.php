<?php

namespace App\Interfaces;

interface TaskInterface
{
    public function execute(): void;

    public function setToScreen(bool $toScreen): self;

    public function setDispatch(bool $dispatch): self;
}
