<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use Modules\Common\Dtos\PostItem;
use Modules\Common\Dtos\PostUpdateItem;
use Modules\Common\Dtos\RemoteImageItem;
use Modules\Common\Events\PostSelectedEvent;
use Modules\Common\Events\PostSelectedQueueableEvent;
use Modules\Common\Events\UpdatePostEvent;
use Modules\Common\Factories\ImageItemFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Services\PostExistsService;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;
use RuntimeException;

abstract class BaseGenerateMediaPostService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    private int $currentChecks = 0;

    private array $usedItems = [];

    protected int $currentItemIndex = 0;

    protected PostUpdateItem $postUpdateItem;

    protected string $mediaIndex = '';

    protected string $mediaType = '';

    protected int $maxChecks = 0;

    public function __construct(
        private readonly PostExistsService $postExistsService,
        private readonly DownloadTrailerService $downloadTrailerService,
        private readonly EncodeTrailerService $encodeTrailerService,
    ) {
        $this->postUpdateItem = new PostUpdateItem;
    }

    abstract public function getPostLimit(): int;

    abstract protected function getTaskName(): string;

    abstract protected function getMediaTypeIcon(Stringable $content): Stringable;

    abstract protected function getTypeBasedContent(array $item, Stringable $content): Stringable;

    abstract protected function getRunTime(array $item, Stringable $content): Stringable;

    abstract protected function processTrailer(array $item): void;

    public function execute(): void
    {
        $movieCount = $this->getPostLimit();

        for ($i = 0; $i < $movieCount; $i++) {
            try {
                $this->currentChecks = 0;
                $this->currentItemIndex = $i;

                $this->warning(sprintf("\nRequesting $this->mediaType %d of %d\n", $i + 1, $movieCount));

                $this->processItem();
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }

    protected function convertRunTime(int $timeTicks): string
    {
        $seconds = $timeTicks / 10000000;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $result = '';
        if ($hours > 0) {
            $result .= $hours.' hour'.($hours > 1 ? 's' : '').' ';
        }

        if ($minutes > 0) {
            $result .= $minutes.' minute'.($minutes > 1 ? 's' : '');
        }

        return trim($result);
    }

    /**
     * @throws Exception
     */
    private function processItem(): void
    {
        $mediaItem = $this->getItem();
        $postItem = $this->getPostItem($mediaItem);

        $this->processTrailer($mediaItem);

        $message = "Dispatching %s event for $this->mediaType: $postItem->title";

        if ($this->queueable) {
            $this->line(sprintf($message, 'PostSelectedQueueableEvent'));

            PostSelectedQueueableEvent::dispatch($postItem);

            return;
        }

        $this->line(sprintf($message, 'PostSelectedEvent'));

        PostSelectedEvent::dispatch(
            $postItem,
            $this->toScreen
        );

        $this->line('PostSelectedEvent Event dispatched.');

        if ($this->postUpdateItem->mediaFiles->isEmpty()) {
            $this->warning("Couldn't get trailers for $this->mediaType {$this->postUpdateItem->title}");

            return;
        }

        // If the process is not queued then the download/encoding runs
        // ahead of this point and updating the posting needs to run
        // right after the Post is created.
        $this->line(sprintf($message, 'UpdatePostEvent'));

        usleep(400000);

        UpdatePostEvent::dispatch(
            $this->postUpdateItem,
            $this->toScreen
        );

        $this->line('UpdatePostEvent Event dispatched.');
    }

    /**
     * @throws Exception
     */
    private function getPostItem(array $item): PostItem
    {
        try {
            $priority = random_int(200, 299);
        } catch (Exception) {
            $priority = 200;
        }

        return new PostItem(
            modelId: (int) $item['Id'],
            identifier: $item['Id'],
            title: $item['Name'],
            content: $this->parseContent($item),
            generator: strtoupper(
                "$this->mediaType={$item['Id']}:RUNNER=$this->EMBY_MEDIA:TASK={$this->getTaskName()}"
            ),
            source: $item['Type'],
            origin: $this->EMBY_MEDIA,
            tasker: $this->getTaskName(),
            priority: $priority,
            responses: $item,
            mediaFiles: $this->parseImages($item),
            hashtags: $this->parseTags($item),
            fromAi: false,
            image: '',
            attribution: '',
        );
    }

    /**
     * @return array<string, string>
     * @throws Exception
     */
    private function getItem(): array
    {
        $client = new Client(
            Config::string('meilisearch.host'),
            Config::string('meilisearch.key'),
        );

        $index = $client->index($this->mediaIndex);

        $stats = $index->stats();
        $total = (int) $stats['numberOfDocuments'];

        return $this->findUnusedItem($total, $index);
    }

    /**
     * @throws Exception
     */
    private function findUnusedItem(int $total, Indexes $index): array
    {
        $this->line("Find a $this->mediaType");

        if ($this->currentChecks >= $this->maxChecks) {
            $this->error('findUnusedMovie ran too many times');

            throw new RuntimeException("No $this->mediaType found");
        }

        if (count($this->usedItems) >= $total) {
            $message = "All {$this->mediaType}s have been used";
            $this->error($message);

            throw new RuntimeException($message);
        }

        $randomOffset = random_int(0, $total - 1);

        $results = $index->search('', [
            'limit' => 1,
            'offset' => $randomOffset,
        ]);

        if (empty($results) || $results->getHitsCount() === 0) {
            ++$this->currentChecks;
            $this->warning("Got empty results. Tries so far: $this->currentChecks");

            usleep(500000);

            return $this->findUnusedItem($total, $index);
        }

        $item = $results->getHit(0);

        if (in_array($item['Id'], $this->usedItems, true)) {
            ++$this->currentChecks;

            return $this->findUnusedItem($total, $index);
        }

        if ($this->postExistsService->exists($item['Id'], $item['Name'])) {
            $this->line("$this->mediaType Post already exists, skipping...\n");

            $this->usedItems[] = $item['Id'];
            ++$this->currentChecks;

            return $this->findUnusedItem($total, $index);
        }

        $this->line("Found {$item['Name']}");

        $this->usedItems[] = $item['Id'];

        return $item;
    }

    /**
     * @param array<string, string> $item
     */
    private function parseContent(array $item): string
    {
        $content = str("[**{$item['Name']} ({$item['ProductionYear']})**]")
            ->append('(')
            ->append(sprintf(Config::string('emby-api.item_url'), $item['Id']))
            ->append(') ');

        $content = $this->getMediaTypeIcon($content);

        if (! empty($item['UserData']) && $item['UserData']['IsFavorite']) {
            $content = $content->append('♥️');
        }

        $content = $content->trim()
            ->append("\n\n");

        if (! empty($item['Taglines'])) {
            $content = $content->append($item['Taglines'][0])
                ->append("\n\n");
        }

        $content = $content->append($item['Overview'])
            ->append("\n\n");

        $content = $this->getTypeBasedContent($item, $content);

        $content = $this->getRunTime($item, $content);

        if (! empty($item['UserData'])) {
            $content = $content->append('Played: ')
                ->append(
                    $item['UserData']['Played'] ? '✅' : '🚫'
                )
                ->append("\n");
        }

        $content = $content->append("Released: {$item['ProductionYear']}")
            ->append("\n");

        if (! empty($item['CriticRating'])) {
            $content = $content->append("Critics: 🍅 {$item['CriticRating']}%")
                ->append("\n");
        }

        if (! empty($item['OfficialRating'])) {
            $content = $content->append("Rated: {$item['OfficialRating']}")
                ->append("\n");
        }

        if (! empty($item['People'])) {
            $content = $content->append("\n\n");

            $director = $this->getDirector($item['People']);
            if ($director) {
                $content = $content->append("Directed by: $director")
                    ->append("\n");
            }

            $cast = $this->getCastMembers($item['People']);
            if ($cast) {
                $content = $content->append("Cast:\n$cast")
                    ->append("\n");
            }
        }

        $text = str(nl2br($content->trim()->value()));

        return $text->replace(["\n", "\r", "\t"], "")
            ->trim()
            ->replace("<br /><br /><br /><br />", "<br /><br />")
            ->replace("<br /><br /><br />", "<br /><br />")
            ->trim()
            ->value();
    }

    /**
     * @param array<array<<string, string>> $people
     */
    private function getDirector(array $people): string
    {
        $director = '';

        foreach ($people as $person) {
            if ($person['Type'] !== 'Director') {
                continue;
            }

            return "[{$person['Name']}]("
                . sprintf(Config::string('emby-api.item_url'), $person['Id'])
                .")";
        }

        return $director;
    }

    /**
     * @param array<string, string> $people
     */
    private function getCastMembers(array $people): string
    {
        $cast = '';
        $actors = array_slice($people, 0, 6);

        foreach ($actors as $actor) {
            if ($actor['Type'] !== 'Actor') {
                continue;
            }

            $role = '';
            if (! empty($actor['Role'])) {
                $role = $actor['Role'];
            }

            $artist = empty($role)
                ? $actor['Name']
                : "{$actor['Name']} ($role)";

            $cast .= "[$artist]("
                . sprintf(Config::string('emby-api.item_url'), $actor['Id'])
                . ")\n";
        }

        return $cast;
    }

    /**
     * parseMedia Method.
     *
     * @param array<string, string> $item
     * @return Collection<RemoteImageItem>
     * @throws Exception
     */
    private function parseImages(array $item): Collection
    {
        $images = collect();

        if (! empty($item['ImageTags'])) {
            $images->add(
                ImageItemFactory::getItem(
                    sprintf(Config::string('emby-api.image_url'), $item['Id'], 'Primary')
                )
            );
        }

        if (! empty($item['BackdropImageTags'])) {
            $images->add(
                ImageItemFactory::getItem(
                    sprintf(Config::string('emby-api.image_url'), $item['Id'], 'Backdrop')
                )
            );
        }

        return $images;
    }

    /**
     * @param array<string, string> $item
     * @return Collection<string>
     */
    private function parseTags(array $item): Collection
    {
        $tags = collect();

        if (! empty($item['Type'])) {
            $tags->add($item['Type']);
        }

        if (! empty($item['TagItems'])) {
            foreach ($item['TagItems'] as $tag) {
                $tags->add($tag['Name']);
            }
        }

        if (! empty($item['Genres'])) {
            $tags = $tags->merge($item['Genres']);
        }

        return $tags;
    }
}
