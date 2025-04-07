<?php

namespace Modules\Common\Dtos;

use Illuminate\Support\Collection;

class PostUpdateItem
{
    public function __construct(
        public string $identifier = '',
        public string $title = '',
        public Collection $mediaFiles = new Collection(),
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
