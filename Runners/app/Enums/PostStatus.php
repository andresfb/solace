<?php

namespace App\Enums;

enum PostStatus: string
{
    case CREATED = 'C';
    case PUBLISHED = 'P';
    case USED = 'U';
    case VIEWED = 'V';
}
