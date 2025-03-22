<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Modules\Common\Dtos\PostItem;
use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Factories\EmptyRunFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Factories\ContentSourceFactory;
use Modules\MediaLibraryRunner\Jobs\ContentSourceJob;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

class MigrateLostCauseService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $libraryPosts = LibraryPost::query()
            ->lostCause()
            ->withBanded()
            ->oldest()
            ->limit(
                config("$this->LOST_CAUSE.posts_limit")
            )
            ->get();

        if ($libraryPosts->isEmpty()) {
            $message = 'There are no lost causes.';

            $this->warning($message);

            EmptyRunFactory::handler(
                $this->MODULE_NAME,
                $this->LOST_CAUSE,
                $message
            );
        }

        foreach ($libraryPosts as $libraryPost) {
            if ($this->queueable) {
                $this->line('Queueing ContentSourceJob for LibraryPost: '.$libraryPost->id);

                ContentSourceJob::dispatch($libraryPost)
                    ->onConnection($this->getConnection($this->MODULE_NAME))
                    ->onQueue($this->getQueue($this->MODULE_NAME))
                    ->delay(now()->addSeconds(5));

                return;
            }

            $this->line('Loading the Generating new Content...');

            $newPost = ContentSourceFactory::loadContent($libraryPost);
            if (! $newPost instanceof LibraryPost) {
                continue;
            }

            $this->line('Dispatching the PostSelectedEvent.');

            PostSelectedEvent::dispatch(
                PostItem::from($newPost->getPostableInfo()),
                $this->toScreen
            );

            $this->line('PostSelectedEvent Event dispatched.');
        }
    }
}
