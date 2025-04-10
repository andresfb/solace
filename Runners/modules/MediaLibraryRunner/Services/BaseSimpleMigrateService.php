<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Common\Events\PostSelectedEvent;
use Modules\Common\Exceptions\EmptyRunException;
use Modules\Common\Factories\EmptyRunFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\MediaLibraryRunner\Jobs\CreatePostItemJob;
use Modules\MediaLibraryRunner\Models\Posts\LibraryPost;
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

            EmptyRunFactory::handler(
                $this->MODULE_NAME,
                $this->getTaskName(),
                $message
            );
        }

        /** @var LibraryPost $libraryPost */
        foreach ($libraryPosts as $libraryPost) {
            if ($this->queueable) {
                $this->line('Queueing CreatePostItemJob for LibraryPost: '.$libraryPost->id);

                // We use a job here as the process will load the media files
                CreatePostItemJob::dispatch($libraryPost, $this->getTaskName())
                    ->onConnection($this->getConnection($this->MODULE_NAME))
                    ->onQueue($this->getQueue($this->MODULE_NAME))
                    ->delay(now()->addSeconds(5));

                return;
            }

            $this->line('Loading the Media Files and tags...');

            PostSelectedEvent::dispatch(
                $libraryPost->getPostableInfo($this->getTaskName()),
                $this->toScreen
            );

            $this->line('PostSelectedEvent Event dispatched.');
        }
    }
}
