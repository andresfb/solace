<?php

namespace App\Enums;

enum PostPrivacy: string
{
    case PRIVATE = 'V';
    case PUBLIC = 'P';
    case FRIENDS = 'F';
}
