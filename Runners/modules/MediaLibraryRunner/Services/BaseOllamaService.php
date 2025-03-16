<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Cloudstudio\Ollama\Facades\Ollama as OllamaFacade;
use Cloudstudio\Ollama\Ollama;
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
use Modules\MediaLibraryRunner\Models\Media\MediaItem;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

abstract class BaseOllamaService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    private array $failureResponses;

    protected MediaItem $mediaInfo;

    public function __construct()
    {
        $this->failureResponses = [
            'i cannot create',
            'promotes nudity',
            'is there anything else i can help you with',
        ];
    }

    abstract protected function getTaskName(): string;

    abstract protected function getRunnerStatus(): RunnerStatus;

    abstract protected function loadMediaInfo(LibraryPost $libraryPost): void;

    abstract protected function getExtraOllamaOptions(Ollama $ollama): Ollama;

    public function execute(LibraryPost $libraryPost): void
    {
        try {
            $this->loadMediaInfo($libraryPost);

            $this->line('Asking the AI for Post content');

            $ollama = OllamaFacade::model(config("{$this->getTaskName()}.ai_model"))
                ->agent(config("{$this->getTaskName()}.ai_agent"))
                ->options([
                    'temperature' => 0.8,
                    'top_p' => 0.8,
                ])
                ->keepAlive('5m')
                ->prompt(config("{$this->getTaskName()}.ai_post_prompt_content"));

            $content = $this->getExtraOllamaOptions($ollama)->ask();

            $this->line('Processing Post');

            $this->processPost($libraryPost, $content);
        } catch (NoAiContentException $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage());

            ChangeStatusEvent::dispatch(
                $this->MEDIA_LIBRARY,
                $libraryPost->id,
                $this->getRunnerStatus()
            );
        } catch (GuzzleException|Exception $e) {
            $this->error($e->getMessage());
            Log::error(
                sprintf(
                    "%s %s %s %s",
                    "@".__CLASS__.".execute.",
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
        if (empty($contentResponse['response'])) {
            throw new \RuntimeException('We did not receive a Content from the AI: '.print_r($contentResponse, true));
        }

        $content = str($contentResponse['response']);
        if (! $content->contains('#') || $content->trim()->contains($this->failureResponses)) {
            throw new NoAiContentException('The AI did not provide usable content');
        }

        [$hashtags, $content] = $this->extractHashtags($content->toString());

        if (empty($hashtags) || empty($content)) {
            throw new \RuntimeException('We did not receive a Content from the AI: '.print_r($contentResponse, true));
        }

        $postInfo = $libraryPost->getPostableInfo();
        $postInfo['fromAi'] = true;
        $postInfo['generator'] .= strtoupper(':AI_MODEL='.config("{$this->getTaskName()}.ai_model"));
        $postInfo['hashtags'] = $postInfo['hashtags']->merge($hashtags);
        $postInfo['content'] = $content;
        // TODO: add a field to save the original AI response into the model settings field

//        dd($postInfo);

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
        $textWithoutHashtags = str($textWithoutHashtags)->trim()
            ->replace('  ', '')
            ->rtrim("\n\r")
            ->rtrim(' ')
            ->toString();

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
