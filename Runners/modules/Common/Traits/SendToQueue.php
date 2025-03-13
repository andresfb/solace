<?php

declare(strict_types=1);

namespace Modules\Common\Traits;

trait SendToQueue
{
    protected bool $queueable = true;

    public function setQueueable(bool $queueable): self
    {
        $this->queueable = $queueable;

        return $this;
    }
}
