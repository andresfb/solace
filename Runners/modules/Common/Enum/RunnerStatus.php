<?php

declare(strict_types=1);

namespace Modules\Common\Enum;

enum RunnerStatus: int
{
    case STASIS = 0;
    case PUBLISHED = 1;
    case UNUSABLE = 2; // Post doesn't have usable media files
    case REPROCESS = 3; // Rejected by the AI Vision model
    case LOST_CAUSE = 4; // Got error from the Chat AI model
}
