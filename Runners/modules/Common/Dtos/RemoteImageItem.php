<?php

namespace Modules\Common\Dtos;

final readonly class RemoteImageItem
{
    public function __construct(
        public string $url,
        public string $type = 'image',
    ) {}
}
