<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Common\Dtos\PostItem;
use Modules\MediaLibraryRunner\Events\PostSelectedQueueableEvent;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;

class CreatePostItemJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly LibraryPost $libraryPost, private readonly string $taskName) {}

    public function handle(): void
    {
        PostSelectedQueueableEvent::dispatch(
            PostItem::from(
                $this->libraryPost->getPostableInfo($this->taskName)
            )
        );
    }
}
