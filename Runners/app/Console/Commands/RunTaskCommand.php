<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\Common\Interfaces\TaskInterface;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class RunTaskCommand extends Command
{
    protected $signature = 'run:task';

    protected $description = 'Run a single Task Runner';

    public function handle(): int
    {
        try {
            $tasks = app('tasks');

            $list = [];
            foreach ($tasks as $task) {
                $word = collect(explode('\\', (string) $task))->last();
                $parts = (array) preg_split('/(?=[A-Z])/', (string) $word);
                $key = implode(' ', $parts);
                $list[$key] = $task;
            }

            clear();
            $this->line('');

            $key = select(
                label: 'Select a task to run',
                options: array_keys($list),
                scroll: 15,
            );

            $taskClass = $list[$key];
            $taskInstance = app($taskClass);
            if (! $taskInstance instanceof TaskInterface) {
                throw new \RuntimeException('Invalid task class');
            }

            $queueable = confirm('Send to queue?', false);

            warning("Running task: $key");
            pause('Press ENTER to continue.');

            $taskInstance->setToScreen(true)
                ->setQueueable($queueable)
                ->execute();

            info("\n\nDone...\n");

            return 0;
        } catch (Exception $e) {
            $this->info('');
            $this->error('Error running task');
            $this->error($e->getMessage());
            $this->info('');

            return 1;
        }
    }
}
