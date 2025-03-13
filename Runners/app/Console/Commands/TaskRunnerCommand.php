<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TaskRunnerService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Common\Enum\TaskRunnerSchedule;

class TaskRunnerCommand extends Command
{
    protected $signature = 'task:runner
                            {frequency : h=Every Hour, eth=Every Two Hours, ttd=Three Times a Day, od=Once Daily, ow=Once a Weekly, om=Once a Monthly}
                            {screen? : Send output to the screen}';

    protected $description = 'Command to run all registered tasks';

    public function handle(TaskRunnerService $service): int
    {
        $queueable = true;
        $toScreen = ! blank($this->argument('screen'));

        try {
            $frequency = strtolower($this->argument('frequency'));
            $schedule = TaskRunnerSchedule::fromString($frequency);

            if ($toScreen) {
                $this->info("Start running tasks...\n");

                $queueable = $this->confirm('Send tasks to queue?');
            }

            $service->setToScreen($toScreen)
                ->setQueueable($queueable)
                ->execute($schedule);

            if ($toScreen) {
                $this->info("\nDone\n");
            }

            return 0;
        } catch (Exception $e) {
            $message = 'Error running tasks: '.$e->getMessage();
            Log::error($message);

            if ($toScreen) {
                $this->error($message);
            }

            return 1;
        }
    }
}
