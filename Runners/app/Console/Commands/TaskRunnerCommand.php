<?php

namespace App\Console\Commands;

use App\Services\TaskRunnerService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TaskRunnerCommand extends Command
{
    protected $signature = 'task:runner {screen?}';

    protected $description = 'Command to run all registered tasks';

    public function handle(TaskRunnerService $service): int
    {
        $dispatch = true;
        $toScreen = ! blank($this->argument('screen'));

        try {
            if ($toScreen) {
                $this->info("Start running tasks...\n");

                $dispatch = $this->confirm('Send tasks to queue?');
            }

            $service->setToScreen($toScreen)
                ->setDispatch($dispatch)
                ->execute();

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
