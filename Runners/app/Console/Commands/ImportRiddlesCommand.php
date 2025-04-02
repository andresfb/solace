<?php

namespace App\Console\Commands;

use App\Services\RiddlesService;
use Exception;
use Illuminate\Console\Command;

class ImportRiddlesCommand extends Command
{
    protected $signature = 'import:riddles';

    protected $description = 'Import Riddles from Riddles API';

    public function handle(RiddlesService $service): int
    {
        try {
            $this->info("\nStarting...\n");

            $service->setToScreen(true)
                ->execute();

            $this->info("\nDone at: ".now()."\n");

            return 0;
        } catch (Exception $e) {
            $this->info('');
            $this->error('Error Importing Riddles');
            $this->error($e->getMessage());
            $this->info('');

            return 1;
        }
    }
}
