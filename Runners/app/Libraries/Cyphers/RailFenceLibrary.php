<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers;

final readonly class RailFenceLibrary extends BaseCypherLibrary implements CypherInterface
{
    private const int RAILS = 2;

    protected function getIdentifier(): string
    {
        return 'Rail Fence';
    }

    public function encode(string $text): string
    {
        $fence = array_fill(0, self::RAILS, []);
        $rail = 0;
        $direction = 1;

        foreach (str_split($text) as $char) {
            $fence[$rail][] = $char;
            $rail += $direction;

            if ($rail === 0 || $rail === self::RAILS - 1) {
                $direction *= -1;
            }
        }

        return implode('', array_merge(...$fence));
    }

    public function decode(string $text): string
    {
        $len = strlen($text);
        $fence = array_fill(0, self::RAILS, array_fill(0, $len, '\n'));
        $rail = 0;
        $direction = 1;

        for ($i = 0; $i < $len; $i++) {
            $fence[$rail][$i] = '*';
            $rail += $direction;
            if ($rail === 0 || $rail === self::RAILS - 1) {
                $direction *= -1;
            }
        }

        $index = 0;
        for ($r = 0; $r < self::RAILS; $r++) {
            for ($c = 0; $c < $len; $c++) {
                if ($fence[$r][$c] === '*') {
                    $fence[$r][$c] = $text[$index++];
                }
            }
        }

        $output = '';
        $rail = 0;
        $direction = 1;
        for ($i = 0; $i < $len; $i++) {
            $output .= $fence[$rail][$i];
            $rail += $direction;

            if ($rail === 0 || $rail === self::RAILS - 1) {
                $direction *= -1;
            }
        }

        return $output;
    }
}
