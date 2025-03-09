<?php

namespace Modules\UserGeneratorRunner\Dtos;

readonly class UserPicture
{
    public function __construct(
        public string $url,
        public int    $usage,
    ) { }
}
