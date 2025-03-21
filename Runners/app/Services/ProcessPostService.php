<?php

declare(strict_types=1);

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
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\Common\Traits\Screenable;
use Modules\MediaLibraryRunner\Models\Media\MediaItem;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Throwable;

class ProcessPostService
{
    use Screenable;

    /**
     * @var array<string>
     */
    private array $extraTags = [];

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

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                RunnerStatus::PUBLISHED,
                $postItem->source,
            );

            return;
        }

        if ($postItem->mediaFiles->isEmpty()) {
            $message = "No media files found for Library Post: $postItem->libraryPostId";

            $this->line($message);
            Log::notice($message);

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                RunnerStatus::UNUSABLE,
            );

            return;
        }

        DB::beginTransaction();

        try {
            $this->line('Saving Post '.$postItem->libraryPostId);

            $post = Post::create([
                'hash' => $postItem->getHash(),
                'user_id' => $this->service->getUser()->id,
                'content' => $this->getContent($postItem),
                'generator' => $postItem->generator,
                'status' => PostStatus::CREATED,
                'privacy' => PostPrivacy::PUBLIC,
                'responses' => $postItem->responses,
            ]);

            if ($post === null) {
                throw new \RuntimeException("Failed to create post from Library Post: $postItem->libraryPostId");
            }

            $this->saveMedia($post, $postItem->mediaFiles);

            $this->saveHashtags($post, $postItem->hashtags);

            DB::commit();

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                RunnerStatus::PUBLISHED,
                $postItem->source,
            );

            // TODO: create a listener in the Host code for PostCreatedEvent where we can add a random number of likes.
            // TODO: create another listener in the Host code for PostCreatedEvent where we can add random AI generated comments (ollama).

            $this->line('Post saved...');
        } catch (FileDoesNotExist|FileIsTooBig $e) {
            DB::rollBack();

            $message = 'File error: '.$e->getMessage();
            $this->error($message);
            Log::error("@ProcessPostService.execute. Error with LibraryPostingId $postItem->libraryPostId: $message");

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->libraryPostId,
                RunnerStatus::UNUSABLE
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
        $hashtags = $hashtags->merge($this->extraTags)
            ->unique();

        $this->line('Saving Hashtags. '.$hashtags->count());

        $post->hashtags()->sync(
            $hashtags->map(fn ($tag) => Hashtag::firstOrCreate(['name' => $tag]))->pluck('id')
        );

        $this->line('Hashtags Saved.');
    }

    private function getContent(PostItem $postItem): string
    {
        $source = str($postItem->source);

        if ($source->startsWith('quote')) {
            $this->extraTags[] = $postItem->title;

            return $postItem->content;
        }

        if ($source->startsWith('joke')) {
            $this->extractTag($postItem->content);
        }

        $content = str($postItem->content)
            ->replace('**Category:**', '')
            ->replace('*Category:*', '')
            ->trim();

        foreach ($this->extraTags as $extraTag) {
            $content = $content->replace("*$extraTag*", '')
                ->trim();
        }

        $title = str($postItem->title)
            ->trim()
            ->replace('...', '');

        if ($title->isEmpty()) {
            return $content->toString();
        }

        if ($content->startsWith([$title->toString(), 'Word Definition'])) {
            return $content->trim()
                ->toString();
        }

        return $content->prepend(
            $title->title()
                ->prepend('**')
                ->append('**')
                ->append("\n\n")
                ->toString()
        )
            ->toString();
    }

    private function extractTag(string $text): void
    {
        // Use regex to find words surrounded by asterisks or the word "NSFW"
        preg_match_all('/\*{1,2}([^*]+)\*{1,2}|NSFW/i', $text, $matches);

        // Flatten the matches array and filter out empty values
        $results = array_filter(array_map('trim', $matches[0]));

        // Remove asterisks and colons from the results
        $cleanedResults = array_map(static fn ($item): string => trim($item, '*:'), $results);

        $cleanedResults = array_values(
            array_unique(
                array_map('strtolower', $cleanedResults)
            )
        );

        foreach ($cleanedResults as $index => $cleanedResult) {
            if ($cleanedResult !== 'category') {
                continue;
            }

            unset($cleanedResults[$index]);
            break;
        }

        $cleanedResults = array_map(static fn ($item) => str($item)->title()
            ->replace(' ', '')
            ->toString(), $cleanedResults);

        $this->extraTags = array_unique(array_merge($this->extraTags, $cleanedResults));
    }
}
