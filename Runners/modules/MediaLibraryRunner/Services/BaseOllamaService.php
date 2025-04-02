<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\ApiConsumers\Services\Ollama;
use Modules\Common\Dtos\PostItem;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\Common\Models\MediaItem;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Events\PostSelectedQueueableEvent;
use Modules\MediaLibraryRunner\Exceptions\NoAiContentException;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;
use Modules\MediaLibraryRunner\Traits\HashtagsExtractable;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;
use RuntimeException;

abstract class BaseOllamaService
{
    use HashtagsExtractable;
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    protected string $spark = '';

    /**
     * @var array|string[]
     */
    private array $failureResponses = [
        'i cannot create',
        'promotes nudity',
        'is there anything else i can help you with',
    ];

    protected MediaItem $mediaInfo;

    public function __construct(
        protected readonly Ollama $ollama,
        protected readonly OpenAiHashtagsService $hashtagsService,
    ) {}

    abstract protected function getTaskName(): string;

    abstract protected function getPrompt(LibraryPost $libraryPost): string;

    abstract protected function getRunnerStatus(): RunnerStatus;

    abstract protected function loadMediaInfo(LibraryPost $libraryPost): void;

    abstract protected function getExtraOllamaOptions(Ollama $ollama): Ollama;

    public function execute(LibraryPost $libraryPost): void
    {
        try {
            $this->loadSpark();

            $this->loadMediaInfo($libraryPost);

            $this->line('Asking the AI for Post content');

            $ollama = $this->ollama->url(config("{$this->getTaskName()}.ai_api_url"))
                ->model(config("{$this->getTaskName()}.ai_model"))
                ->agent(config("{$this->getTaskName()}.ai_agent"))
                ->options([
                    'temperature' => config("{$this->getTaskName()}.ai_temperature"),
                ])
                ->keepAlive('5m')
                ->prompt($this->getPrompt($libraryPost));

            $content = $this->getExtraOllamaOptions($ollama)->ask();

            $this->line('Processing Post');

            $this->processPost($libraryPost, $content);
        } catch (NoAiContentException $e) {
            $this->error($e->getMessage().PHP_EOL);

            Log::error(
                sprintf(
                    "Error with LibPost: %s %s\n%s",
                    $libraryPost->id,
                    $e->getMessage(),
                    print_r($e->response, true)
                )
            );

            ChangeStatusEvent::dispatch(
                $this->MEDIA_LIBRARY,
                $libraryPost->id,
                $this->getRunnerStatus()
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
            Log::error(
                sprintf(
                    '%s %s %s %s',
                    '@'.self::class.'.execute.',
                    'Error found generating AI content for Library Post Id:',
                    $libraryPost->id,
                    $e->getMessage()
                )
            );
        }
    }

    private function loadSpark(): void
    {
        $this->spark = (string) str(config('media_runner.ai_sparks'))
            ->explode(',')
            ->random();
    }

    /**
     * processPost Method.
     *
     * @param  array<string, mixed>  $contentResponse
     *
     * @throws NoAiContentException
     * @throws RuntimeException
     * @throws JsonException
     */
    private function processPost(LibraryPost $libraryPost, array $contentResponse): void
    {
        $response = $contentResponse['response'] ?? '';

        if (empty($response)) {
            throw new RuntimeException(
                'We did not receive a Content from the AI: '.print_r($contentResponse, true)
            );
        }

        $content = str($response);
        if ($content->trim()->contains($this->failureResponses)) {
            throw new NoAiContentException(
                'The AI refused to produce content',
                $contentResponse
            );
        }

        if (! $content->contains('#')) {
            $content = $this->getCleanText($content->toString());
            $hashtags = $this->hashtagsService->setToScreen($this->toScreen)
                ->generateHashtags($content);
        } else {
            [$hashtags, $content] = $this->parseContent($content->toString());
        }

        if (empty($hashtags) || empty($content)) {
            throw new RuntimeException(
                'We did not receive a Content from the AI: '.print_r($contentResponse, true)
            );
        }

        $postInfo = $libraryPost->getPostableInfo($this->getTaskName());

        $postInfo['generator'] .= strtoupper(':AI_MODEL='.config("{$this->getTaskName()}.ai_model").':SPARK='.$this->spark);
        $postInfo['generator'] .= $this->hashtagsService->getGeneratorTag();

        $postInfo['fromAi'] = true;
        $postInfo['hashtags'] = $postInfo['hashtags']->merge($hashtags);
        $postInfo['content'] = $content;
        $postInfo['responses'] = $this->hashtagsService->isGenerated()
            ? json_encode(['ollama' => $contentResponse, 'openai' => $this->hashtagsService->getOpenAiResponse()], JSON_THROW_ON_ERROR)
            : $contentResponse;

        $this->dispatchEvents($postInfo);
    }

    /**
     * @param  array<string, mixed>  $postInfo
     */
    private function dispatchEvents(array $postInfo): void
    {
        $message = "Dispatching %s event for LibraryPost: {$postInfo['libraryPostId']}\n";

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

    /**
     * extractHashtags Method.
     *
     * @return array<int, array<string>|string>
     */
    private function parseContent(string $text): array
    {
        // Extract hashtags into an array
        $hashtags = $this->extractHashtags($text);

        // Remove hashtags from the original text
        $textWithoutHashtags = (string) preg_replace('/#\w+/', '', $text);

        // Trim any extra whitespace
        $textWithoutHashtags = $this->getCleanText($textWithoutHashtags);

        return [$hashtags, $textWithoutHashtags];
    }

    private function getCleanText(string $text): string
    {
        return str($text)
            ->replace('  ', '')
            ->ltrim('"')
            ->rtrim('"')
            ->rtrim("\n\r")
            ->rtrim(' ')
            ->trim()
            ->toString();
    }
}
