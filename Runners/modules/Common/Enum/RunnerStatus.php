<?php

declare(strict_types=1);

namespace Modules\Common\Enum;

enum RunnerStatus: int
{
    case STASIS = 0;
    case PUBLISHED = 3;
    case UNUSABLE = 4;
    case REPROCESS = 5;
}
