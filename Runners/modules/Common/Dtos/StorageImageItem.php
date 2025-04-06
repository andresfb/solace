<?php

namespace Modules\Common\Dtos;

class StorageImageItem
{
    public function __construct(
        public string $filePath,
        public string $type = 'image',
    ) {}
}
