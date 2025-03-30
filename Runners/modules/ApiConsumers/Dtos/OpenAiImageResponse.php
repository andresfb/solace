<?php

declare(strict_types=1);

namespace Modules\ApiConsumers\Dtos;

use OpenAI\Responses\Images\CreateResponse;
use OpenAI\Responses\Images\CreateResponseData;
use Spatie\LaravelData\Data;

class OpenAiImageResponse extends Data
{
    public function __construct(
        public string $image = '',
        public bool $generated = false,
    ) {}

    public static function fromResponse(CreateResponse $response): self
    {
        $aiResponse = new self;

        $data = collect($response->data);
        if ($data->isEmpty()) {
            return $aiResponse;
        }

        /** @var CreateResponseData $item */
        foreach ($data as $item) {
            if (empty($item->url)) {
                continue;
            }

            $aiResponse->generated = true;
            $aiResponse->image = $item->url;

            break;
        }

        return $aiResponse;
    }
}
