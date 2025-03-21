<?php

namespace Modules\MediaLibraryRunner\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Common\Dtos\PostItem;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Factories\ContentSourceFactory;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class ContentSourceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
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
            PostItem::from($newPost->getPostableInfo()),
        );
    }
}
