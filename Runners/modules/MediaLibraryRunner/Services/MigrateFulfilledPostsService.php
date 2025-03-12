<?php

namespace Modules\MediaLibraryRunner\Services;

use Illuminate\Support\Facades\Log;
use Modules\Common\Dtos\PostItem;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Jobs\CreatePostItemJob;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateFulfilledPostsService implements TaskServiceInterface
{
    use ModuleConstants;
    use Screenable;
    use SendToQueue;
    use QueueSelectable;

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
                $this->line('Queueing CreatePostItemJob for LibraryPost: '.$libraryPost->id);

                CreatePostItemJob::dispatch($libraryPost)
                    ->onConnection($this->getConnection($this->MODULE_NAME))
                    ->onQueue($this->getQueue($this->MODULE_NAME))
                    ->delay(now()->addSecond());

                return;
            }

            $this->line('Loading the Media Files and tags...');

            PostSelectedEvent::dispatch(
                PostItem::from($libraryPost->getPostableInfo()),
                $this->toScreen
            );

            $this->line('PostSelectedEvent Event dispatched.');
        });
    }
}
