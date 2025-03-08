<?php

namespace App\Services;

use App\Enums\PostPrivacy;
use App\Enums\PostStatus;
use App\Models\Hashtags\Hashtag;
use App\Models\Posts\Post;
use App\Models\Posts\PostItem;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\MediaRunner\Models\Media\MediaItem;
use Throwable;

readonly class ProcessPostService
{
    public function __construct(private RandomUserSelectorService $service) {}

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function execute(PostItem $postItem): void
    {
        if ($postItem->mediaFiles->isEmpty()) {
            throw new \RuntimeException("No media files found for Library Post: $postItem->libraryPostId");
        }

//        dd($postItem);

        try {
            DB::beginTransaction();

            $post = Post::updateOrCreate([
                'hash' => $postItem->getHash(),
                'user_id' => $this->service->getUser()->id,
            ], [
                'title' => $postItem->title,
                'content' => $postItem->content,
                'source' => $postItem->source,
                'status' => PostStatus::CREATED,
                'privacy' => PostPrivacy::PUBLIC,
            ]);

            if ($post === null) {
                throw new \RuntimeException("Failed to create post from Library Post: $postItem->libraryPostId");
            }

            $this->saveMedia($post, $postItem->mediaFiles);

            $this->saveHashtags($post, $postItem->hashtags);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error while processing post: '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    private function saveMedia(Post $post, Collection $mediaFiles): void
    {
        $mediaFiles->each(function (MediaItem $mediaFile) use ($post): void {
            $post->addMedia($mediaFile->filePath)
                ->withCustomProperties([
                    'original_id' => $mediaFile->originalId,
                    'original_name' => $mediaFile->originalName,
                    'original_file_path' => $mediaFile->filePath,
                ])
                ->toMediaCollection($mediaFile->collectionName);
        });
    }

    private function saveHashtags(Post $post, Collection $hashtags): void
    {
        $post->hashtags()->sync(
            $hashtags->map(fn ($tag) => Hashtag::firstOrCreate(['name' => $tag]))->pluck('id')
        );
    }
}
