<?php

namespace Modules\MediaLibraryRunner\Events;

use App\Models\Posts\PostItem;
use Illuminate\Foundation\Events\Dispatchable;

class PostSelectedQueueableEvent
{
    use Dispatchable;

    public function __construct(public readonly PostItem $postItem)
    {
    }
}
