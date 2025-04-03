<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers;

final readonly class AffineLibrary extends BaseCypherLibrary implements CypherInterface
{
    private const int A = 5;

    private const int B = 8;

    protected function getIdentifier(): string
    {
        return 'Affine';
    }

    public function encode(string $text): string
    {
        $output = '';

        foreach (str_split(strtoupper($text)) as $char) {
            if (ctype_alpha($char)) {
                $x = ord($char) - 65;
                $new_char = chr((((self::A * $x + self::B) % 26) + 65));
                $output .= $new_char;
            } else {
                $output .= $char;
            }
        }

        return $output;
    }

    public function decode(string $text): string
    {
        $output = '';
        $a_inv = 21; // Modular inverse of 5 mod 26

        foreach (str_split(strtoupper($text)) as $char) {
            if (ctype_alpha($char)) {
                $y = ord($char) - 65;
                $new_char = chr(((($a_inv * ($y - self::B + 26)) % 26) + 65));
                $output .= $new_char;
            } else {
                $output .= $char;
            }
        }

        return $output;
    }
}
