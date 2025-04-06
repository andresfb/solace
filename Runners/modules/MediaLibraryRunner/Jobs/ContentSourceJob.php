<?php

namespace Modules\MediaLibraryRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Common\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Factories\ContentSourceFactory;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class ContentSourceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use ModuleConstants;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly LibraryPost $libraryPost) {}

    public function handle(): void
    {
        $newPost = ContentSourceFactory::loadContent($this->libraryPost);
        if (! $newPost instanceof LibraryPost) {
            return;
        }

        PostSelectedEvent::dispatch(
            $newPost->getPostableInfo($this->LOST_CAUSE),
        );
    }
}
