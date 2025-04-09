<?php

namespace App\Services;

use App\Models\Posts\Post;
use Illuminate\Support\Facades\File;
use Modules\Common\Dtos\PostUpdateItem;
use Modules\Common\Traits\Screenable;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class UpdatePostService
{
    use Screenable;

    /** @var array|string[]  */
    private array $collectionType = [
        'mp4' => 'trailer',
    ];

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function execute(PostUpdateItem $postItem): void
    {
        if ($postItem->mediaFiles->isEmpty()) {
            $message = "No trailers found for Movie $postItem->title";

            $this->error($message);

            throw new FileDoesNotExist($message);
        }

        $this->line('Finding Post to update...');

        $post = $this->findPost($postItem);

        $this->line('Saving trailer files');

        $file = '';
        foreach ($postItem->mediaFiles as $mediaFile) {
            $file = $mediaFile;
            $ext = strtolower(pathinfo($mediaFile, PATHINFO_EXTENSION));

            $collection = array_key_exists($ext, $this->collectionType)
                ? $this->collectionType[$ext]
                : 'trailer-image';

            $post->addMedia($mediaFile)
                ->toMediaCollection($collection);
        }

        $this->line('Deleting temporary directory...');

        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (File::exists($path)) {
            File::deleteDirectory($path);
        }
    }

    private function findPost(PostUpdateItem $postItem): Post
    {
        $count = 3;
        while ($count > 0) {
            --$count;

            $post = Post::where('hash', $postItem->getHash())
                ->first();

            if ($post === null) {
                usleep(300000);

                continue;
            }

            return $post;
        }

        $message = "No post found for $postItem->title, {$postItem->getHash()}";
        $this->error($message);

        throw new \RuntimeException($message);
    }
}
