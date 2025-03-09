<?php

namespace Modules\Common\Enum;

enum TaskRunnerSchedule
{
    case HOURLY;
    case EVERY_TWO_HOURS;
    case THREE_TIMES_DAY;
    case ONCE_DAILY;
    case ONCE_WEEKLY;
    case ONCE_MONTHLY;

    public static function fromString(string $frequency): self
    {
        return match (strtolower($frequency)) {
            'eth' => self::EVERY_TWO_HOURS,
            'ttd' => self::THREE_TIMES_DAY,
            'od' => self::ONCE_DAILY,
            'ow' => self::ONCE_WEEKLY,
            'om' => self::ONCE_MONTHLY,
            default => self::HOURLY,
        };
    }
}
