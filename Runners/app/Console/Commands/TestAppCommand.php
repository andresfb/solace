<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\MediaRunner\Models\Media\LibraryMedia;
use Modules\MediaRunner\Models\Post\LibraryPost;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Tests runner';

    public function handle(): int
    {
        try {
            $this->info("\nStarting test\n");

            $post = LibraryPost::find(25);

            dump($post->getTags()->first());

//            if ($post->item === null) {
//                throw new \RuntimeException('No items available');
//            }
//
//            $fileInfo = [];
//
//            foreach ($post->item->media as $media) {
//                if ($media->collection_name === 'thumb') {
//                    continue;
//                }
//
//                $fileInfo = $media->getFileInfo();
//                break;
//            }
//
//            dump($fileInfo->toArray());

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
