<?php

namespace Modules\MediaLibraryRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Common\Dtos\PostItem;
use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Events\PostSelectedEvent;
use Modules\MediaLibraryRunner\Jobs\CreatePostItemJob;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;
use Modules\MediaLibraryRunner\Traits\ModuleConstants;

abstract class BaseSimpleMigrateService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    abstract protected function getLibraryPosts(): Collection;

    abstract protected function getTaskName(): string;

    abstract protected function getErrorMessage(): string;

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $libraryPosts = $this->getLibraryPosts();

        if ($libraryPosts->isEmpty()) {
            $message = $this->getErrorMessage();

            $this->warning($message);

            throw new EmptyRunException(
                $this->MODULE_NAME,
                $this->getTaskName(),
                $message
            );
        }

        $libraryPosts->each(function (LibraryPost $libraryPost): void {
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
