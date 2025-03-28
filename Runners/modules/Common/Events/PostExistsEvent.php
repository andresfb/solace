<?php

declare(strict_types=1);

namespace Modules\Common\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PostExistsEvent
{
    use Dispatchable;

    /**
     * @var callable
     */
    public $callback;

    public function __construct(
        public string $identifier,
        public string $title,
        callable $callback
    ) {
        $this->callback = $callback;
    }
}
