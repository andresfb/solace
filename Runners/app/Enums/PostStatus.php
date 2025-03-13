<?php

declare(strict_types=1);

namespace App\Enums;

enum PostStatus: string
{
    case CREATED = 'C';
    case PUBLISHED = 'P';
    case USED = 'U';
    case VIEWED = 'V';
}
