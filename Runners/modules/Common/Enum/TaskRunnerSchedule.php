<?php

declare(strict_types=1);

namespace Modules\Common\Enum;

enum TaskRunnerSchedule: string
{
    case HALF_HOUR = 'lf';
    case HOURLY = 'h';
    case ODD_HOUR = 'odd';
    case EVERY_TWO_HOURS = 'eth';
    case THREE_TIMES_DAY = 'ttd';
    case ONCE_DAILY = 'od';
    case ONCE_WEEKLY = 'ow';
    case ONCE_MONTHLY = 'om';
    case CUSTOM = 'cm';

    public static function fromString(string $frequency): self
    {
        return match (strtolower($frequency)) {
            'h' => self::HOURLY,
            'odd' => self::ODD_HOUR,
            'eth' => self::EVERY_TWO_HOURS,
            'ttd' => self::THREE_TIMES_DAY,
            'od' => self::ONCE_DAILY,
            'ow' => self::ONCE_WEEKLY,
            'om' => self::ONCE_MONTHLY,
            'cm' => self::CUSTOM,
            default => self::HALF_HOUR,
        };
    }
}
