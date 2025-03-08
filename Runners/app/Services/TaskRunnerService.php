<?php

namespace App\Services;

use App\Interfaces\TaskInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class TaskRunnerService
{
    private bool $toScreen = false;

    private bool $dispatch = true;

    public function execute(): void
    {
        $tasks = app('tasks');

        foreach ($tasks as $taskClass) {
            $taskInstance = app($taskClass);
            if (! $taskInstance instanceof TaskInterface) {
                continue;
            }

            if ($this->toScreen) {
                echo "Running task {$taskClass}\n";
            }

            try {
                $taskInstance->setToScreen($this->toScreen)
                    ->setDispatch($this->dispatch)
                    ->execute();

                if ($this->dispatch) {
                    echo "\n\n";
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                Log::error($message);

                if ($this->toScreen) {
                    echo $message.PHP_EOL;
                }
            }
        }
    }

    public function setToScreen(bool $toScreen): TaskRunnerService
    {
        $this->toScreen = $toScreen;

        return $this;
    }

    public function setDispatch(bool $dispatch): TaskRunnerService
    {
        $this->dispatch = $dispatch;

        return $this;
    }
}
