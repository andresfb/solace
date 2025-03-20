<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
//use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
//use Modules\MediaLibraryRunner\Repositories\ContentSourceJokes;
use Throwable;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Tests runner';

    public function handle(): int
    {
        try {
            $this->info("\nStarting test\n");

//            $libraryPost = LibraryPost::query()
//                ->bandedReprocess()
//                ->inRandomOrder()
//                ->firstOrFail();
//
//            dump($libraryPost->toArray());
//
//            $srv = app(ContentSourceJokes::class);
//            $item = $srv->generateContent($libraryPost);
//
//            dump($item->toArray());

            $this->info("\nDone at: ".now()."\n");

            return 0;
        } catch (Exception|Throwable $e) {
            $this->info('');
            $this->error('Error Testing');
            $this->error($e->getMessage());
            $this->info('');

            return 1;
        }
    }
}
