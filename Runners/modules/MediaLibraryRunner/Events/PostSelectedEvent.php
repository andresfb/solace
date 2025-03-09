<?php

namespace Modules\MediaLibraryRunner\Events;

use App\Models\Posts\PostItem;
use Illuminate\Foundation\Events\Dispatchable;

class PostSelectedEvent
{
    use Dispatchable;

    public function __construct(public readonly PostItem $postItem, public readonly bool $queueable = true)
    {
    }
}
