<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers;

final readonly class AtbashLibrary extends BaseCypherLibrary implements CypherInterface
{
    protected function getIdentifier(): string
    {
        return 'Atbash';
    }

    public function encode(string $text): string
    {
        $alphabet = range('A', 'Z');
        $reversed = array_reverse($alphabet);
        $cipher_map = array_combine($alphabet, $reversed);

        $text = strtoupper($text);
        $output = '';
        foreach (str_split($text) as $char) {
            $output .= $cipher_map[$char] ?? $char;
        }

        return $output;
    }

    public function decode(string $text): string
    {
        return $this->encode($text);
    }
}
