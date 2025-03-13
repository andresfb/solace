<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Services;

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

class MigrateViaAiService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly OllamaService $ollamaService) {}

    /**
     * @throws EmptyRunException
     */
    public function execute(): void
    {
        $libraryPosts = LibraryPost::query()
            ->imagePosts()
            ->oldest()
            ->limit(
                config("$this->POST_VIA_AI.posts_limit")
            )
            ->get();

        if ($libraryPosts->isEmpty()) {
            $message = 'No Library Post for AI found';

            $this->warning($message);

            throw new EmptyRunException(
                $this->MODULE_NAME,
                $this->POST_VIA_AI,
                $message
            );
        }

        $libraryPosts->each(function (LibraryPost $libraryPost): void {
            if ($this->queueable) {
//                $this->line('Queueing CreatePostItemJob for LibraryPost: '.$libraryPost->id);
//
//                CreatePostItemJob::dispatch($libraryPost)
//                    ->onConnection($this->getConnection($this->MODULE_NAME))
//                    ->onQueue($this->getQueue($this->MODULE_NAME))
//                    ->delay(now()->addSecond());

                return;
            }

            $this->line('Sending Library post to the AI...');

            $this->ollamaService->setToScreen($this->toScreen)
                ->execute($libraryPost);
        });
    }
}
