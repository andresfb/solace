<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Cloudstudio\Ollama\Facades\Ollama;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\PostCreatedEvent;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Models\Media\MediaItem;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class OllamaService
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
                PostCreatedEvent::dispatch(
                    $this->MEDIA_LIBRARY,
                    $libraryPost->id,
                    RunnerStatus::UNUSABLE
                );
            }

            /** @var MediaItem $mediaInfo */
            $mediaInfo = $mediaFiles->first();
            if (! file_exists($mediaInfo->filePath)) {
                PostCreatedEvent::dispatch(
                    $this->MEDIA_LIBRARY,
                    $libraryPost->id,
                    RunnerStatus::UNUSABLE
                );
            }

            dump($mediaInfo->filePath, round($mediaInfo->fileSize / 1024, 2));

            $this->line('Getting the Post title');
            $title = Ollama::model(config("$this->POST_VIA_AI.ai_vision_model"))
                ->keepAlive('8m')
                ->image($mediaInfo->filePath)
                ->prompt(config("$this->POST_VIA_AI.ai_post_prompt_title"))
                ->ask();

            dump($title['response']);

            $this->line('Getting the Post content');
            $content = Ollama::model(config("$this->POST_VIA_AI.ai_vision_model"))
                ->keepAlive('8m')
                ->image($mediaInfo->filePath)
                ->prompt(config("$this->POST_VIA_AI.ai_post_prompt_content"))
                ->ask();

            dump($content['response']);

            $this->line('Processing Post');

            $this->processPost($libraryPost, $title, $content);
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

    private function processPost(LibraryPost $libraryPost, array $titleResponse, array $contentResponse): void
    {
        if (blank($titleResponse['response'])) {
            // TODO: create a custom Exception and dispatch and event to update the original to runner_status = REPROCESS
            throw new \RuntimeException('We did not receive a Title from the AI: '.print_r($titleResponse, true));
        }

        $title = $titleResponse['response'];

        if (blank($contentResponse['response'])) {
            throw new \RuntimeException('We did not receive a Content from the AI: '.print_r($contentResponse, true));
        }

        $content = str($contentResponse['response']);
        if (! $content->contains('#') || $content->trim()->contains($this->failureResponses)) {
            throw new \RuntimeException('The AI did not provide usable content');
        }

        [$hashtags, $content] = $this->extractHashtags($content->toString());

        dump($hashtags, $content);

        $postInfo = $libraryPost->getPostableInfo();
        $postInfo['fromAi'] = true;
        $postInfo['title'] = $title;
        $postInfo['content'] = $content;
        $postInfo['source'] .= strtoupper(':AI_MODEL='.config("$this->POST_VIA_AI.ai_vision_model"));
        $postInfo['hashtags'] = $postInfo['hashtags']->merge($hashtags);

        dd($postInfo);
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
