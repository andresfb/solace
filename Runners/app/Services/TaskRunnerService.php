<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Common\Enum\TaskRunnerSchedule;
use Modules\Common\Interfaces\TaskInterface;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;

class TaskRunnerService
{
    use Screenable;
    use SendToQueue;

    public function execute(TaskRunnerSchedule $schedule): void
    {
        $tasks = app('tasks');

        foreach ($tasks as $taskClass) {
            $taskInstance = app($taskClass);
            if (! $taskInstance instanceof TaskInterface) {
                continue;
            }

            if ($taskInstance->runSchedule() !== $schedule) {
                continue;
            }

            $this->info("Running task $taskClass\n");

            try {
                $taskInstance->setToScreen($this->toScreen)
                    ->setQueueable($this->queueable)
                    ->execute();

                $this->warning("Task $taskClass executed\n\n");
            } catch (Exception $e) {
                $message = $e->getMessage();

                $this->error($message);
            }
        }
    }
}
