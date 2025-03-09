<?php

namespace App\Jobs;

use App\Models\Posts\PostItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\MediaLibraryRunner\Events\PostSelectedQueueableEvent;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class CreatePostItemJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly LibraryPost $libraryPost)
    {
    }

    public function handle(): void
    {
        PostSelectedQueueableEvent::dispatch(
            PostItem::createFromModel($this->libraryPost),
        );
    }
}
