<?php

namespace App\Services;

use App\Enums\PostPrivacy;
use App\Enums\PostStatus;
use App\Models\Hashtags\Hashtag;
use App\Models\Posts\Post;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Common\Dtos\PostItem;
use Modules\Common\Enum\LibraryPostStatus;
use Modules\Common\Events\PostCreatedEvent;
use Modules\Common\Traits\Screenable;
use Modules\MediaLibraryRunner\Models\Media\MediaItem;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class ProcessPostService
{
    use Screenable;

    public function __construct(private readonly RandomUserSelectorService $service) {}

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function execute(PostItem $postItem): void
    {
        if (Post::where('hash', $postItem->getHash())->exists()) {
            $message = "Posting $postItem->title already exists for $postItem->libraryPostId";

            $this->line($message);
            Log::notice($message);

            PostCreatedEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                LibraryPostStatus::PUBLISHED
            );

            return;
        }

        if ($postItem->mediaFiles->isEmpty()) {
            $message = "No media files found for Library Post: $postItem->libraryPostId";

            $this->line($message);
            Log::notice($message);

            PostCreatedEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                LibraryPostStatus::UNUSABLE
            );

            return;
        }

        DB::beginTransaction();

        try {
            $this->line('Saving Post '.$postItem->title);

            $post = Post::create([
                'hash' => $postItem->getHash(),
                'user_id' => $this->service->getUser()->id,
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

            PostCreatedEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                LibraryPostStatus::PUBLISHED
            );

            // TODO: create a listener in the Host code for PostCreatedEvent where we can add a random number of likes.
            // TODO: create another listener in the Host code for PostCreatedEvent where we can add random AI generated comments (ollama).

            $this->line('Post saved...');
        } catch (FileDoesNotExist|FileIsTooBig $e) {
            DB::rollBack();

            $message = 'File error: '.$e->getMessage();
            $this->error($message);
            Log::error("@ProcessPostService.execute. Error with LibraryPostingId $postItem->libraryPostId: $message");

            PostCreatedEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                LibraryPostStatus::UNUSABLE
            );

            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            $message = 'Error while processing post: '.$e->getMessage();
            $this->error($message);
            Log::error("@ProcessPostService.execute. Error with LibraryPostingId $postItem->libraryPostId: $message");

            throw $e;
        } finally {
            $this->line("\n");
        }
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function saveMedia(Post $post, Collection $mediaFiles): void
    {
        $this->line('Saving Media Files. '.$mediaFiles->count());

        $mediaFiles->each(function (MediaItem $mediaFile) use ($post): void {
            $post->addMedia($mediaFile->filePath)
                ->preservingOriginal()
                ->withCustomProperties([
                    'original_id' => $mediaFile->originalId,
                    'original_name' => $mediaFile->originalName,
                    'original_file_path' => $mediaFile->filePath,
                ])
                ->toMediaCollection($mediaFile->collectionName);
        });

        $this->line('Media Files Saved.');
    }

    private function saveHashtags(Post $post, Collection $hashtags): void
    {
        $this->line('Saving Hashtags. '.$hashtags->count());

        $post->hashtags()->sync(
            $hashtags->map(fn ($tag) => Hashtag::firstOrCreate(['name' => $tag]))->pluck('id')
        );

        $this->line('Hashtags Saved.');
    }
}
