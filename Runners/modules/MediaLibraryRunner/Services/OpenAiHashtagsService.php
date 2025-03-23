<?php

namespace Modules\MediaLibraryRunner\Services;

use Modules\ApiConsumers\Services\OpenAiClient;
use Modules\Common\Traits\Screenable;
use Modules\MediaLibraryRunner\Exceptions\NoAiContentException;
use Modules\MediaLibraryRunner\Traits\HashtagsExtractable;

class OpenAiHashtagsService
{
    use HashtagsExtractable;
    use Screenable;

    private bool $generated = false;

    private string $openAiResponse = '';

    public function __construct(private readonly OpenAiClient $client) {}

    public function isGenerated(): bool
    {
        return $this->generated;
    }

    public function getOpenAiResponse(): string
    {
        return $this->openAiResponse;
    }

    public function getGeneratorTag(): string
    {
        if (! $this->generated) {
            return '';
        }

        return strtoupper(":HASHTAGS_MODEL={$this->client->getModel()}");
    }

    /**
     * @throws NoAiContentException
     */
    public function generateHashtags(string $text): array
    {
        $this->generated = false;
        $this->openAiResponse = '';

        $this->line('Asking OpenAI for hashtags');

        $aiResponse = $this->client->setAgentPrompt($text)
            ->setUserPrompt(config('media_runner.ai_hashtags_prompt'))
            ->ask();

        if (blank($aiResponse->content)) {
            $this->error('We did not get a response from the AI');

            throw new NoAiContentException(
                'Failed to generate hashtags.',
                $aiResponse->response ? (array) $aiResponse->response : []
            );
        }

        $this->generated = true;
        $this->openAiResponse = $aiResponse->response;

        $this->line('Got a response from AI. Extracting the tags');

        return $this->extractHashtags($aiResponse->content);
    }
}
