<?php

declare(strict_types=1);

namespace Modules\Common\Enum;

enum TaskRunnerSchedule: string
{
    case HALF_HOUR = 'lf';
    case HOURLY = 'h';
    case HOURLY_05 = 'h5';
    case ODD_HOUR = 'odd';
    case EVERY_TWO_HOURS = 'eth';
    case ONCE_DAILY = 'od';
    case ONCE_DAILY_WEEK_DAYS = 'odw';
    case TWICE_DAILY = 'td';
    case THREE_TIMES_DAY = 'ttd';
    case ONCE_WEEKLY = 'ow';
    case THREE_TIMES_WEEKLY = 'ttw';
    case ONCE_MONTHLY = 'om';
    case TWICE_MONTHLY = 'tm';
    case CUSTOM = 'cm';

    public static function fromString(string $frequency): self
    {
        return match (strtolower($frequency)) {
            'h' => self::HOURLY,
            'h5' => self::HOURLY_05,
            'odd' => self::ODD_HOUR,
            'eth' => self::EVERY_TWO_HOURS,
            'od' => self::ONCE_DAILY,
            'odw' => self::ONCE_DAILY_WEEK_DAYS,
            'td' => self::TWICE_DAILY,
            'ttd' => self::THREE_TIMES_DAY,
            'ow' => self::ONCE_WEEKLY,
            'ttw' => self::THREE_TIMES_WEEKLY,
            'om' => self::ONCE_MONTHLY,
            'tm' => self::TWICE_MONTHLY,
            'cm' => self::CUSTOM,
            default => self::HALF_HOUR,
        };
    }
}
