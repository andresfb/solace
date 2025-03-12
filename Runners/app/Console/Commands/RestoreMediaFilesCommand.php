<?php

namespace App\Console\Commands;

use App\Models\Posts\Post;
use Exception;
use Illuminate\Console\Command;

class RestoreMediaFilesCommand extends Command
{
    protected $signature = 'restore:media-files';

    protected $description = 'Command description';

    public function handle(): int
    {
        try {
            $this->info("\nStarting restore\n");

            $posts = Post::query()
                ->with(['media'])
                ->get();

            foreach ($posts as $post) {
                foreach ($post->getMedia('image') as $media) {
                    $destination = $media->getCustomProperty('original_file_path');
                    if (file_exists($destination)) {
                        continue;
                    }

                    $url = $media->getTemporaryUrl(now()->addMinutes(10));
                    $fileContent = file_get_contents($url);

                    if ($fileContent === false) {
                        $this->error("Error: $url.");
                    }

                    $result = file_put_contents($destination, $fileContent);

                    if ($result === false) {
                        $this->error("Error: $destination.");
                    }

                    $this->line('');
                }
            }

            $this->info("\nDone at: ".now()."\n");

            return 0;
        } catch (Exception $e) {
            $this->info('');
            $this->error('Error restoring');
            $this->error($e->getMessage());
            $this->info('');

            return 1;
        }
    }
}
