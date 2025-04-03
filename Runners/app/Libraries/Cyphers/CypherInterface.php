<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers;

interface CypherInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getClue(): string;

    public function encode(string $text): string;

    public function decode(string $text): string;
}
