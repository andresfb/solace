<?php

namespace Modules\MediaRunner\Services;

use App\Jobs\ProcessPostJob;
use App\Models\Posts\PostItem;
use App\Services\ProcessPostService;
use Illuminate\Support\Facades\Log;
use Modules\MediaRunner\Models\Post\LibraryPost;

class MigrateFulfilledPostsService
{
    private bool $dispatch = true;

    public function __construct(private readonly ProcessPostService $postService) {}

    public function execute(): void
    {
        $libraryPosts = LibraryPost::query()
            ->tagged()
            ->withoutBanded()
            ->oldest()
            ->get();

        if ($libraryPosts->isEmpty()) {
            Log::info('No Unpublished LibraryPosts found.');

            return;
        }

        $libraryPosts->each(function (LibraryPost $libraryPost): void {
            $libraryPost->source = 'media-library';

            if ($this->dispatch) {
                // TODO: set up the queues and send this to it with a delay
                ProcessPostJob::dispatch(
                    PostItem::createFromModel($libraryPost)
                );

                return;
            }

            try {
                $this->postService->execute(
                    PostItem::createFromModel($libraryPost)
                );
            } catch (\Throwable $e) {
                Log::error($e);
            }
        });
    }

    public function setDispatch(bool $dispatch): self
    {
        $this->dispatch = $dispatch;

        return $this;
    }
}
