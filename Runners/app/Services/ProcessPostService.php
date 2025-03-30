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
use Modules\Common\Exceptions\NoImageException;
use Modules\Common\Models\MediaItem;
use Modules\Common\Traits\Screenable;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
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
     * @throws Throwable
     */
    public function execute(PostItem $postItem): void
    {
        if (Post::where('hash', $postItem->getHash())->exists()) {
            $message = "Posting $postItem->title already exists for $postItem->modelId";

            $this->line("$message\n");
            Log::notice($message);

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->modelId,
                RunnerStatus::PUBLISHED,
                $postItem->source,
            );

            return;
        }

        if ($postItem->mediaFiles->isEmpty()) {
            $message = "No media files found for Model Id: $postItem->modelId";

            $this->line("$message\n");
            Log::notice($message);

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->modelId,
                RunnerStatus::UNUSABLE,
            );

            return;
        }

        try {
            DB::beginTransaction();

            $this->line('Saving Post '.$postItem->modelId);

            $post = Post::create([
                'hash' => $postItem->getHash(),
                'user_id' => $this->service->getUser()->id,
                'content' => str(
                    nl2br($this->getContent($postItem))
                )
                ->replace("\r", '')
                ->replace('<br /><br /><br /><br />', '<br /><br />')
                ->value(),
                'tasker' => $postItem->tasker,
                'generator' => $postItem->generator,
                'status' => PostStatus::CREATED,
                'privacy' => PostPrivacy::PUBLIC,
                'responses' => $postItem->responses,
            ]);

            if ($post === null) {
                throw new \RuntimeException(
                    "Failed to create post from Library Post: $postItem->modelId"
                );
            }

            if ($postItem->mediaFiles->isEmpty()) {
                $this->saveImage($post, $postItem->image);
            } else {
                $this->saveMedia($post, $postItem->mediaFiles);
            }

            $this->saveHashtags($post, $postItem->hashtags);

            DB::commit();

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->modelId,
                RunnerStatus::PUBLISHED,
                $postItem->source,
            );

            // TODO: create a listener in the Host code for PostCreatedEvent where we can add a random number of likes.
            // TODO: create another listener in the Host code for PostCreatedEvent where we can add random AI generated comments (ollama).

            $this->line('Post saved...');
        } catch (FileDoesNotExist|FileIsTooBig|FileCannotBeAdded|NoImageException $e) {
            DB::rollBack();

            $message = 'File error: '.$e->getMessage();
            $this->error($message);
            Log::error("@ProcessPostService.execute. Error with LibraryPostingId $postItem->modelId: $message");

            ChangeStatusEvent::dispatch(
                $postItem->origin,
                $postItem->modelId,
                RunnerStatus::UNUSABLE
            );

            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            $message = 'Error while processing post: '.$e->getMessage();
            $this->error($message);
            Log::error("@ProcessPostService.execute. Error with LibraryPostingId $postItem->modelId: $message");

            throw $e;
        } finally {
            $this->line("\n");
        }
    }

    /**
     * @throws NoImageException
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function saveImage(Post $post, string $image): void
    {
        if (blank($image)) {
            throw new NoImageException("$post->generator doesn't have a image");
        }

        $this->line("Saving image Files: $image");

        $post->addMediaFromUrl($image)
            ->toMediaCollection('image');

        $this->line('Image Saved.');
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
        $contentTrimmed = trim($postItem->content);
        $titleTrimmed = str($postItem->title)
            ->replace('...', '')
            ->trim();

        $source = str($postItem->source);

        if ($source->startsWith('quote')) {
            $this->extraTags[] = $titleTrimmed->value();

            return $contentTrimmed === '' ? $titleTrimmed->value() : $contentTrimmed;
        }

        if ($source->startsWith('joke')) {
            $this->extractTag($postItem->content);
        }

        if ($contentTrimmed === '') {
            return $titleTrimmed->value();
        }

        $content = str($contentTrimmed)
            ->replace('**Category:**', '')
            ->replace('*Category:*', '')
            ->trim();

        foreach ($this->extraTags as $extraTag) {
            $content = $content->replace("*$extraTag*", '')
                ->trim();
        }

        $contentLow = $content->lower();
        $titleLow = $titleTrimmed->lower()->value();

        if ($titleTrimmed->isEmpty()
            || $titleTrimmed->contains('Word Definition')
            || $source->contains(['bible', 'quran'])
            || $contentLow->startsWith($titleLow)
            || str($postItem->generator)->contains('AI_MODEL')) {
            return $content->trim()->value();
        }

        return $content->prepend(
            $titleTrimmed->title()
                ->prepend('**')
                ->append('**')
                ->trim()
                ->append("<br /><br />")
                ->value()
        )
        ->trim()
        ->value();
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
            ->value(), $cleanedResults);

        $this->extraTags = array_unique(array_merge($this->extraTags, $cleanedResults));
    }
}
