<?php

namespace App\Console\Commands;

use App\Services\ProfileImageGenService;
use Exception;
use Illuminate\Console\Command;
use Modules\Common\Dtos\PostItem;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Tests runner';

    public function handle(): int
    {
        try {
            $this->info("\nStarting test\n");

            $srv = new ProfileImageGenService();
            $image = $srv->generateImage("sample@example.com");

            dump($image);


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
