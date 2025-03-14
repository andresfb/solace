<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Services\OllamaVisionService;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Tests runner';

    public function handle(): int
    {
        try {
            $this->info("\nStarting test\n");

            $randomOffset = random_int(0, max(0, 200000 - 10));

            $posts = LibraryPost::query()
                ->imagePosts()
                ->skip($randomOffset)
                ->take(10)
                ->get();

            $post = $posts->random();

            $srv = new OllamaVisionService();
            $srv->setToScreen(true)
                ->execute($post);

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
