<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Posts\Post;
use Modules\Common\Events\PostExistsEvent;

class PostExistsListener
{
    public function handle(PostExistsEvent $event): void
    {
        $result = Post::where(
            'hash',
            md5("$event->identifier|$event->title")
        )->exists();

        call_user_func($event->callback, $result);
    }
}
