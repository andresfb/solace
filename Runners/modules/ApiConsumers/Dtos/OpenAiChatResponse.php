<?php

declare(strict_types=1);

namespace Modules\ApiConsumers\Dtos;

use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateResponseChoice;
use Spatie\LaravelData\Data;

class OpenAiChatResponse extends Data
{
    public function __construct(
        public string $content = '',
        public string $response = '',
    ) {}

    public static function fromResponse(CreateResponse $response): self
    {
        $aiResponse = new self;

        $choice = collect($response->choices)->first();
        if (! $choice instanceof CreateResponseChoice) {
            return $aiResponse;
        }

        $aiResponse->content = $choice->message->content ?? '';

        try {
            $serialized = json_encode($response, JSON_THROW_ON_ERROR);

            $aiResponse->response = $serialized;
        } catch (\JsonException) {
            $aiResponse->response = serialize($aiResponse);
        } finally {
            return $aiResponse;
        }
    }
}
