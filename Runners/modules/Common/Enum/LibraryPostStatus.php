<?php

namespace Modules\Common\Enum;

enum LibraryPostStatus: int
{
    case CREATED = 0;
    case TAGGED = 1;
    case DISABLED = 2;
    case PUBLISHED = 3;
    case UNUSABLE = 4;
}
