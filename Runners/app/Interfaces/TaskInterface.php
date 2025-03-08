<?php

namespace App\Interfaces;

// TODO: move this plus the Traits and the base library to "Shared" module so the other modules dont couple themselves with the Host

interface TaskInterface
{
    public function execute(): void;

    public function setToScreen(bool $toScreen): self;

    public function setDispatch(bool $dispatch): self;
}
