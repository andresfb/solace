<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

use Illuminate\Support\Str;

trait QueueSelectable
{
    public function getConnection(string $section): string
    {
        return config("$section.queue_connection");
    }

    /**
     * @param string $section
     * @param int $number
     * @return string|array<string>
     */
    public function getQueue(string $section, int $number = 1): string|array
    {
        $queues = Str::of(config("$section.queues"))
            ->explode(',')
            ->random($number);

        if ($number > 1) {
            return $queues->toArray();
        }

        return $queues[0];
    }
}
