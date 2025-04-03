<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers;

final readonly class Rot13Library extends BaseCypherLibrary implements CypherInterface
{
    protected function getIdentifier(): string
    {
        return 'ROT13';
    }

    public function encode(string $text): string
    {
        return str_rot13($text);
    }

    public function decode(string $text): string
    {
        return $this->encode($text);
    }
}
