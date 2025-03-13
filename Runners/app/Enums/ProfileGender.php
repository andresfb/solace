<?php

declare(strict_types=1);

namespace App\Enums;

enum ProfileGender: string
{
    case MALE = 'M';
    case FEMALE = 'F';
    case NONE_BINARY = 'N';
    case OTHER = 'O';
    case PREFER_NOT_TO_SAY = 'P';

    public static function fromString(string $gender): self
    {
        return match (strtolower($gender)) {
            'male' => self::MALE,
            'female' => self::FEMALE,
            default => self::OTHER,
        };
    }
}
