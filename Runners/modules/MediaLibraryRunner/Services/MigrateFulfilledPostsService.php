<?php

namespace Modules\MediaLibraryRunner\Services;

use App\Jobs\ProcessPostJob;
use App\Models\Posts\PostItem;
use App\Services\ProcessPostService;
use App\Traits\Screenable;
use Illuminate\Support\Facades\Log;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class MigrateFulfilledPostsService
{
    use Screenable;

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

            // TODO: change this functionality to use and Event/Listener model and prevent the module from using Host classes
            if ($this->dispatch) {
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
}
