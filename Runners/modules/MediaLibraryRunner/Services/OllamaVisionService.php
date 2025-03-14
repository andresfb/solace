<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Cloudstudio\Ollama\Facades\Ollama;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Modules\Common\Dtos\PostItem;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Events\PostSelectedQueueableEvent;
use Modules\MediaLibraryRunner\Exceptions\NoAiContentException;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class OllamaVisionService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    private array $failureResponses;

    public function __construct()
    {
        $this->failureResponses = [
            'i cannot create',
            'promotes nudity',
            'is there anything else i can help you with',
        ];
    }

    public function execute(LibraryPost $libraryPost): void
    {
        try {
            $this->line('Asking the AI for Post content');

            $mediaFiles = $libraryPost->getMediaFiles();
            if ($mediaFiles->isEmpty()) {
                ChangeStatusEvent::dispatch(
                    $this->MEDIA_LIBRARY,
                    $libraryPost->id,
                    RunnerStatus::UNUSABLE
                );
            }

            $mediaInfo = $mediaFiles->first();
            if (! file_exists($mediaInfo->filePath)) {
                ChangeStatusEvent::dispatch(
                    $this->MEDIA_LIBRARY,
                    $libraryPost->id,
                    RunnerStatus::UNUSABLE
                );
            }

            $this->line('Getting the Post content');

            $content = Ollama::model(config("$this->POST_VIA_AI.ai_vision_model"))
                ->keepAlive('8m')
                ->image($mediaInfo->filePath)
                ->prompt(config("$this->POST_VIA_AI.ai_post_prompt_content"))
                ->ask();

            $this->line('Processing Post');

            $this->processPost($libraryPost, $content);
        } catch (NoAiContentException $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage());

            ChangeStatusEvent::dispatch(
                $this->MEDIA_LIBRARY,
                $libraryPost->id,
                RunnerStatus::REPROCESS
            );
        } catch (GuzzleException|Exception $e) {
            $this->error($e->getMessage());
            Log::error(
                sprintf(
                    "%s %s %s %s",
                    "@OllamaService.execute.",
                    "Error found generating AI content for Library Post Id:",
                    $libraryPost->id,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @throws NoAiContentException
     */
    private function processPost(LibraryPost $libraryPost, array $contentResponse): void
    {
        if (blank($contentResponse['response'])) {
            throw new NoAiContentException('We did not receive a Content from the AI: '.print_r($contentResponse, true));
        }

        $content = str($contentResponse['response']);
        if (! $content->contains('#') || $content->trim()->contains($this->failureResponses)) {
            throw new NoAiContentException('The AI did not provide usable content');
        }

        [$hashtags, $content] = $this->extractHashtags($content->toString());

        $postInfo = $libraryPost->getPostableInfo();
        $postInfo['fromAi'] = true;
        $postInfo['content'] = $content;
        $postInfo['source'] .= strtoupper(':AI_MODEL='.config("$this->POST_VIA_AI.ai_vision_model"));
        $postInfo['hashtags'] = $postInfo['hashtags']->merge($hashtags);

        $this->dispatchEvents($postInfo);
    }

    private function dispatchEvents(array $postInfo): void
    {
        $message = "Dispatching %s event for LibraryPost: {$postInfo['libraryPostId']}";

        if ($this->queueable) {
            $this->line(sprintf($message, 'PostSelectedQueueableEvent'));

            PostSelectedQueueableEvent::dispatch(
                PostItem::from($postInfo)
            );

            return;
        }

        $this->line(sprintf($message, 'PostSelectedEvent'));

        PostSelectedEvent::dispatch(
            PostItem::from($postInfo),
            $this->toScreen
        );
    }

    private function extractHashtags(string $text): array
    {
        // Use regex to find hashtags
        preg_match_all('/#\w+/', $text, $matches);

        // Extract hashtags into an array
        $hashtags = $matches[0];

        // Remove hashtags from the original text
        $textWithoutHashtags = preg_replace('/#\w+/', '', $text);

        // Trim any extra whitespace
        $textWithoutHashtags = trim($textWithoutHashtags);

        // Remove the '#' symbol from each hashtag
        $cleanedHashtags = array_map(static function($hashtag) {
            return ltrim($hashtag, '#');
        }, $hashtags);

        return [
            $cleanedHashtags,
            $textWithoutHashtags,
        ];
    }
}
