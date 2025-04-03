<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers;

use App\Libraries\Cyphers\Dtos\CypherItem;
use App\Models\Cyphers\Cypher;

class CypherFactory
{
    public static function getLibrary(string $className): CypherInterface
    {
        $cypherInstance = app($className);

        if (! $cypherInstance instanceof CypherInterface) {
            throw new \RuntimeException("$className does not implement CypherInterface");
        }

        return $cypherInstance;
    }

    public static function encodeText(string $text, string $className): CypherItem
    {
        $lib = self::getLibrary($className);

        return new CypherItem(
            id: $lib->getId(),
            encodedText: $lib->encode($text),
        );
    }

    public static function encodeWithRandom(string $text): CypherItem
    {
        $cypher = Cypher::query()
            ->where('active', true)
            ->inRandomOrder()
            ->firstOrFail();

        return self::encodeText($text, $cypher->class);
    }
}
