<?php

namespace App\Services;

use App\Interfaces\TaskInterface;
use App\Traits\Screenable;
use Exception;
use Illuminate\Support\Facades\Log;

class TaskRunnerService
{
    use Screenable;

    public function execute(): void
    {
        $tasks = app('tasks');

        foreach ($tasks as $taskClass) {
            $taskInstance = app($taskClass);
            if (! $taskInstance instanceof TaskInterface) {
                continue;
            }

            $this->line("Running task $taskClass");

            try {
                $taskInstance->setToScreen($this->toScreen)
                    ->setDispatch($this->dispatch)
                    ->execute();

                $this->line("\n");
            } catch (Exception $e) {
                $message = $e->getMessage();
                Log::error($message);

                $this->error($message);
            }
        }
    }
}
