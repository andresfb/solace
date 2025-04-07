<?php

declare(strict_types=1);

namespace Modules\Common\Dtos;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class PostItem extends Data
{
    /**
     * @param  array<string, mixed>|null  $responses
     */
    public function __construct(
        public int $modelId,
        public string $identifier,
        public string $title,
        public string $content,
        public string $generator,
        public string $source,
        public string $origin,
        public string $tasker,
        public int $priority,
        public ?array $responses,
        public Collection $mediaFiles,
        public Collection $hashtags,
        public bool $fromAi = false,
        public string $image = '',
        public string $attribution = '',
    ) {}

    public function getHash(): string
    {
        return md5(sprintf(
            '%s|%s',
            trim($this->identifier),
            trim($this->title),
        ));
    }
}
