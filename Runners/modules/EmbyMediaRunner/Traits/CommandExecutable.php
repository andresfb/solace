<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Traits;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

trait CommandExecutable
{
    private function executeCommand(string $cmd): void
    {
        $this->line("Executing command: $cmd");

        $process = Process::fromShellCommandline($cmd)
            ->enableOutput()
            ->setTimeout(0)
            ->mustRun();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = str($process->getOutput());
        if ($result->lower()->contains('error')) {
            throw new RuntimeException($result->value());
        }
    }
}
