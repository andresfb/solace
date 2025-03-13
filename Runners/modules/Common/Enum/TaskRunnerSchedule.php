<?php

declare(strict_types=1);

namespace Modules\Common\Enum;

enum TaskRunnerSchedule: string
{
    case HOURLY = 'h';
    case EVERY_TWO_HOURS = 'eth';
    case THREE_TIMES_DAY = 'ttd';
    case ONCE_DAILY = 'od';
    case ONCE_WEEKLY = 'ow';
    case ONCE_MONTHLY = 'om';

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
