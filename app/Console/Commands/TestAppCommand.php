<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\MediaRunner\Models\LibraryPost;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Tests runner';

    public function handle(): int
    {
        try {
            $this->info("\nStarting test\n");

            $post = LibraryPost::find(15474);

            dump($post->toArray());

            $this->info("\nDone at: " . now() . "\n");

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
