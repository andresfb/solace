<?php

namespace Modules\MediaLibraryRunner\Services;

use App\Jobs\CreatePostItemJob;
use App\Models\Posts\PostItem;
use App\Services\ProcessPostService;
use Illuminate\Support\Facades\Log;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateFulfilledPostsService
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;
    use QueueSelectable;

    public function __construct(private readonly ProcessPostService $postService) {}

    public function execute(): void
    {
        $libraryPosts = LibraryPost::query()
            ->tagged()
            ->withoutBanded()
            ->oldest()
            ->limit(
                config("$this->MIGRATE_FULFILLED.posts_limit")
            )
            ->get();

        if ($libraryPosts->isEmpty()) {
            $message = 'No Unpublished LibraryPosts found.';

            $this->warning($message);
            Log::info($message);

            return;
        }

        $libraryPosts->each(function (LibraryPost $libraryPost): void {
            $libraryPost->source = $this->MEDIA_LIBRARY;

            if ($this->queueable) {
                $this->line('Dispatching CreatePostItemJob for LibraryPost: '.$libraryPost->id);

                CreatePostItemJob::dispatch($libraryPost)
                    ->onConnection($this->getConnection($this->MIGRATE_FULFILLED))
                    ->onQueue($this->getQueue($this->MIGRATE_FULFILLED))
                    ->delay(now()->addSecond());

                return;
            }

            $this->line('Loading the Media Files and tags...');

            PostSelectedEvent::dispatch(
                PostItem::createFromModel($libraryPost)
            );

            $this->line('Event dispatched.');
        });
    }
}
