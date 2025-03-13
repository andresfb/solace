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

    public function getQueue(string $section, int $number = 1)
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
