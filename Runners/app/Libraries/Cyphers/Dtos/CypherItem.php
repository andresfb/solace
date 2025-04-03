<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers\Dtos;

final readonly class CypherItem
{
    public function __construct(
        public int $id,
        public string $encodedText,
    ) {}
}
