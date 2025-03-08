<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Carbon;

/**
 * Trait ConvertDateTimeToTimezone
 *
 * Always treats dates as DateTime objects and adjusts their timezone
 * to app.timezone, if different.
 *
 * In combination with Eloquent\Model protected $dates, this trait can ensure
 * that:
 *   a) whatever date (string, \DateTime, \Carbon\Carbon) will always
 *      be treated as \DateTime
 *   b) If the timezone of that \DateTime differs from app.timezone, it will
 *      be converted to it
 *
 * This is useful if you have app.timezone *and* want all dates in the database
 * to be in app.timezone too.
 *
 * Requirement:
 * - the date attributes name must be added to the models protected
 *   $dates white-list
 *
 * Note: if you pass a \DateTime and changing the timezone is necessary, a
 * clone will be created so your original \DateTime is *not* mutated.
 */
trait ConvertDateTimeToTimezone
{
    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     * @return Carbon
     * @throws Exception
     */
    protected function asDateTime(mixed $value): Carbon
    {
        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and delegate it immediately to the parent
        if (is_numeric($value)) {
            return parent::asDateTime($value);
        }

        // Flag to prevent creating a \DateTime from a string and then later
        // cloning it unnecessarily
        $justCreated = false;

        if (!($value instanceof \DateTime)) {
            $value = new \DateTime($value);
            $justCreated = true;
        }

        if ($value->getTimezone()->getName() !== config('app.timezone')) {
            if (!$justCreated) {
                $value = clone $value;
            }
            $value->setTimezone(new \DateTimeZone(config('app.timezone')));
        }

        return parent::asDateTime($value);
    }
}
