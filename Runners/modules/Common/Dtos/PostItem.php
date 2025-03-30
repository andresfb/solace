<?php

declare(strict_types=1);

namespace Modules\Common\Dtos;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class PostItem extends Data
{
    private string $hash;

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
        public ?array $responses,
        public Collection $mediaFiles,
        public Collection $hashtags,
        public bool $fromAi = false,
        public string $image = '',
    ) {}

    public function getHash(): string
    {
        if (! isset($this->hash) || ($this->hash === '' || $this->hash === '0')) {
            $this->hash = md5("$this->identifier|$this->title");
        }

        return $this->hash;
    }
}
