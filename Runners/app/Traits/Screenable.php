<?php

namespace App\Traits;

trait Screenable
{
    private bool $toScreen = false;

    private bool $dispatch = true;

    public function setDispatch(bool $dispatch): self
    {
        $this->dispatch = $dispatch;

        return $this;
    }

    public function setToScreen(bool $toScreen): self
    {
        $this->toScreen = $toScreen;

        return $this;
    }

    public function line(string $message): void
    {
        if (!$this->toScreen) {
            return;
        }

        echo $message . PHP_EOL;
    }

    private function info(string $message): void
    {
        if (!$this->toScreen) {
            return;
        }

        $green = "\033[32m";
        $reset = "\033[0m";

        echo $green . $message . $reset . PHP_EOL;
    }

    private function error(string $error): void
    {
        if (!$this->toScreen) {
            return;
        }

        $red = "\033[31m";
        $reset = "\033[0m";

        echo $red . $error . $reset . PHP_EOL;
    }

    private function warning(string $warning): void
    {
        if (!$this->toScreen) {
            return;
        }

        $orange = "\033[33m";
        $reset = "\033[0m";

        echo $orange . $warning . $reset . PHP_EOL;
    }
}
