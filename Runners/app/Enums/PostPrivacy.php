<?php

declare(strict_types=1);

namespace App\Enums;

enum PostPrivacy: string
{
    case PRIVATE = 'V';
    case PUBLIC = 'P';
    case FRIENDS = 'F';
}
