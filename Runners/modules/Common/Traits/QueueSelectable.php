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
        return (string) $this->getRandomQueues($section)
            ->firstOrFail();
    }

    public function getRandomQueues(string $section, int $number = 1): Collection
    {
        return collect($this->getQueues($section))
            ->random($number);
    }

    public function getQueues(string $section): array
    {
        return Str::of(config("$section.queues"))
            ->explode(',')
            ->toArray();
    }
}
