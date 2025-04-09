<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

use Illuminate\Support\Facades\Log;

trait Screenable
{
    protected bool $toScreen = false;

    public function setToScreen(bool $toScreen): self
    {
        $this->toScreen = $toScreen;

        return $this;
    }

    public function character(string $character): void
    {
        if (! $this->toScreen) {
            return;
        }

        echo $character;
    }

    public function line(string $message): void
    {
        if ($message !== '') {
            Log::notice($message);
        }

        if (! $this->toScreen) {
            return;
        }

        echo $message.PHP_EOL;
    }

    private function info(string $message): void
    {
        if ($message !== '') {
            Log::info($message);
        }

        if (! $this->toScreen) {
            return;
        }

        $green = "\033[32m";
        $reset = "\033[0m";

        echo $green.$message.$reset.PHP_EOL;
    }

    private function error(string $error): void
    {
        if ($error !== '') {
            Log::error($error);
        }

        if (! $this->toScreen) {
            return;
        }

        $red = "\033[31m";
        $reset = "\033[0m";

        echo $red.$error.$reset.PHP_EOL;
    }

    private function warning(string $warning): void
    {
        if ($warning !== '') {
            Log::warning($warning);
        }

        if (! $this->toScreen) {
            return;
        }

        $orange = "\033[33m";
        $reset = "\033[0m";

        echo $orange.$warning.$reset.PHP_EOL;
    }
}
