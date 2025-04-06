<?php

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use MeiliSearch\Client;
use Meilisearch\Endpoints\Indexes;
use Modules\Common\Dtos\PostItem;
use Modules\Common\Dtos\RemoteImageItem;
use Modules\Common\Events\PostSelectedEvent;
use Modules\Common\Events\PostSelectedQueueableEvent;
use Modules\Common\Factories\ImageItemFactory;
use Modules\Common\Interfaces\TaskServiceInterface;
use Modules\Common\Services\PostExistsService;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Jobs\DownloadTrailerJob;
use Modules\EmbyMediaRunner\Jobs\EncodeTrailerJob;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;

class GenerateMoviePostService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    private int $maxChecks = 20;

    private int $currentChecks = 0;

    private array $usedMovies = [];

    public function __construct(
        private readonly PostExistsService $postExistsService,
        private readonly DownloadTrailerService $downloadTrailerService,
        private readonly EncodeTrailerService $encodeTrailerService,
    ) {}

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $postItem = $this->getPostItem(
            $this->getMovie()
        );

        $message = "Dispatching %s event for Movie: $postItem->modelId\n";

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
    }

    private function getPostItem(array $movie): PostItem
    {
        try {
            $priority = random_int(200, 299);
        } catch (\Exception) {
            $priority = 200;
        }

        return new PostItem(
            modelId: $movie['Id'],
            identifier: $movie['Id'],
            title: $movie['Name'],
            content: $this->parseContent($movie),
            generator: strtoupper(
                "MOVIE={$movie['Id']}:RUNNER=$this->EMBY_MEDIA:TASK=$this->GENERATE_MOVIE_POST"
            ),
            source: $movie['Type'],
            origin: $this->EMBY_MEDIA,
            tasker: $this->GENERATE_MOVIE_POST,
            priority: $priority,
            responses: $movie,
            mediaFiles: $this->parseMedia($movie),
            hashtags: $this->parseTags($movie),
            fromAi: false,
            image: '',
            attribution: '',
        );
    }

    /**
     * @return array<string, string>
     * @throws Exception
     */
    private function getMovie(): array
    {
        $client = new Client(
            Config::string('meilisearch.host'),
            Config::string('meilisearch.key'),
        );

        $index = $client->index(
            Config::string('meilisearch.movies_index')
        );

        $stats = $index->stats();
        $total = (int) $stats['numberOfDocuments'];

        return $this->findUnusedMovie($total, $index);
    }

    /**
     * @throws Exception
     */
    private function findUnusedMovie(int $total, Indexes $index): array
    {
        if ($this->currentChecks >= $this->maxChecks) {
            throw new \RuntimeException('No Movie found');
        }

        if (count($this->usedMovies) >= $total) {
            throw new \RuntimeException('All movies have been used.');
        }

        $randomOffset = random_int(0, $total - 1);

        $results = $index->search('', [
            'limit' => 1,
            'offset' => $randomOffset,
        ]);

        if (empty($results) || $results->getHitsCount() === 0) {
            ++$this->currentChecks;

            return $this->findUnusedMovie($total, $index);
        }

        $movie = $results->getHit(0);

        if (in_array($movie['Id'], $this->usedMovies, true)) {
            ++$this->currentChecks;

            return $this->findUnusedMovie($total, $index);
        }

        if ($this->postExistsService->exists($movie['Id'], $movie['Name'])) {
            $this->line("Movie Post already exists, skipping...\n");

            $this->usedMovies[] = $movie['Id'];
            ++$this->currentChecks;

            return $this->findUnusedMovie($total, $index);
        }

        return $movie;
    }

    /**
     * @param array<string, string> $movie
     */
    private function parseContent(array $movie): string
    {
        $content = str("[**{$movie['Name']} ({$movie['ProductionYear']})**]")
            ->append('(')
            ->append(sprintf(Config::string('emby-api.item_url'), $movie['Id']))
            ->append(')')
            ->append('<br /><br />');

        if (! empty($movie['Taglines'])) {
            $content = $content->append($movie['Taglines'][0])
                ->append('<br /><br />');
        }

        $content = $content->append($movie['Overview'])
            ->append('<br /><br />');

        $content = $content->append("Released: {$movie['ProductionYear']}")
            ->append('<br />');

        if (! empty($movie['CriticRating'])) {
            $content = $content->append("Critics: 🍅 {$movie['CriticRating']}%")
                ->append('<br />');
        }

        if (! empty($movie['OfficialRating'])) {
            $content = $content->append("Rated: {$movie['OfficialRating']}")
                ->append('<br />');
        }

        if (! empty($movie['RunTimeTicks'])) {
            $content = $content->append(
                'Runtime: '.
                $this->convertRunTime((int) $movie['RunTimeTicks'])
            )
            ->append('<br />');
        }

        if (! empty($movie['People'])) {
            $content = $content->append('<br /><br />');

            $director = $this->getDirector($movie['People']);
            if ($director) {
                $content = $content->append("Directed by: {$director}")
                    ->append('<br />');
            }

            $cast = $this->getCastMembers($movie['People']);
            if ($cast) {
                $content = $content->append("Cast:<br />$cast")
                    ->append('<br />');
            }
        }

        $content = $content->replace('<br /><br /><br /><br />', '<br /><br />')
            ->replace('<br /><br /><br />', '<br /><br />')
            ->trim();

        if ($content->endsWith('<br /><br />')) {
            $content = $content->replace('<br /><br />', '<br />');
        }

        if (! $content->endsWith('<br />')) {
            $content = $content->append('<br />');
        }

        return $content->trim()->value();
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

            $cast .= "[{$actor['Name']}]("
                . sprintf(Config::string('emby-api.item_url'), $actor['Id'])
                . ")<br />";
        }

        return $cast;
    }

    /**
     * parseMedia Method.
     *
     * @param array<string, string> $movie
     * @return Collection<RemoteImageItem>
     */
    private function parseMedia(array $movie): Collection
    {
        $images = collect();

        if (! empty($movie['ImageTags'])) {
            $images->add(
                ImageItemFactory::getItem(
                    sprintf(Config::string('emby-api.image_url'), $movie['Id'], 'Primary')
                )
            );
        }

        if (! empty($movie['BackdropImageTags'])) {
            $images->add(
                ImageItemFactory::getItem(
                    sprintf(Config::string('emby-api.image_url'), $movie['Id'], 'Backdrop')
                )
            );
        }

        $this->processTrailer($movie);

        return $images;
    }

    /**
     * @param array<string, string> $movie
     */
    private function processTrailer(array $movie): void
    {
        $item = new ProcessMediaItem(
            $movie['Id'],
            $movie['Name'],
        );

        if (! empty($movie['RemoteTrailers'])) {
            $item = $item->withTrailerUrl($movie['RemoteTrailers'][0]['Url']);

            if ($this->queueable) {
                DownloadTrailerJob::dispatch($item)
                    ->onConnection($this->getConnection('trailer-download'))
                    ->onQueue($this->getQueue('trailer-download'))
                    ->delay(now()->addMinute());

                return;
            }

            $this->downloadTrailerService->setToScreen($this->toScreen)
                ->setQueueable($this->queueable)
                ->execute($item);

            return;
        }

        $item = $item->withFilePath($movie['Path']);

        if ($this->queueable) {
            EncodeTrailerJob::dispatch($item)
                ->onConnection($this->getConnection('encode-trailer'))
                ->onQueue($this->getQueue('encode-trailer'))
                ->delay(now()->addMinute());

            return;
        }

        $this->encodeTrailerService->setToScreen($this->toScreen)
            ->setQueueable($this->queueable)
            ->execute($item);
    }

    /**
     * @param array<string, string> $movie
     * @return Collection<string>
     */
    private function parseTags(array $movie): Collection
    {
        $tags = collect();

        if (! empty($movie['Type'])) {
            $tags->add($movie['Type']);
        }

        if (! empty($movie['TagItems'])) {
            $tags = $tags->merge($movie['TagItems']);
        }

        if (! empty($movie['Genres'])) {
            $tags = $tags->merge($movie['Genres']);
        }

        return $tags;
    }

    private function convertRunTime(int $timeTicks): string
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
}
