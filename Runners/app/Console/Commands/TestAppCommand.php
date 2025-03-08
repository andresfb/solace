<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\UserGeneratorRunner\Services\RandomUserService;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Tests runner';

    public function handle(): int
    {
        try {
            $this->info("\nStarting test\n");

            $srv = app(RandomUserService::class);
            $userList = $srv->execute();

            $this->info("\nDone at: ".now()."\n");

            return 0;
        } catch (Exception $e) {
            $this->info('');
            $this->error('Error Testing');
            $this->error($e->getMessage());
            $this->info('');

            return 1;
        }
    }
}
