<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait QueueSelectable
{
    public function getConnection(string $section): string
    {
        return config("$section.queue_connection");
    }

    public function getQueue(string $section): string
    {
        return (string) $this->getQueues($section)
            ->firstOrFail();
    }

    public function getQueues(string $section, int $number = 1): Collection
    {
        return Str::of(config("$section.queues"))
            ->explode(',')
            ->random($number)
            ->collect();
    }
}
