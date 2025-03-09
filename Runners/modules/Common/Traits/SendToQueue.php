<?php

namespace Modules\Common\Traits;

trait SendToQueue
{
    private bool $queueable = true;

    public function setQueueable(bool $queueable): self
    {
        $this->queueable = $queueable;

        return $this;
    }
}
