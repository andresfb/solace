<?php

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use MeiliSearch\Client;
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
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Factories\MovieTrailerFactory;
use Modules\EmbyMediaRunner\Jobs\DownloadTrailerJob;
use Modules\EmbyMediaRunner\Jobs\EncodeTrailerJob;
use Modules\EmbyMediaRunner\Traits\ModuleConstants;

final class GenerateMoviePostService implements TaskServiceInterface
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    private int $maxChecks;

    private int $currentChecks = 0;

    private int $currentMovieIndex = 0;

    private array $usedMovies = [];

    private PostUpdateItem $postUpdateItem;

    public function __construct(
        private readonly PostExistsService $postExistsService,
        private readonly DownloadTrailerService $downloadTrailerService,
        private readonly EncodeTrailerService $encodeTrailerService,
    ) {
        $this->postUpdateItem = new PostUpdateItem;
        $this->maxChecks = Config::integer('generate-movie-post.max_movie_checks');
    }

    public function execute(): void
    {
        $movieCount = Config::integer('generate-movie-post.posts_limit');

        for ($i = 0; $i < $movieCount; $i++) {
            try {
                $this->currentChecks = 0;
                $this->currentMovieIndex = $i;

                $this->warning(sprintf("\nRequesting Movie %d of %d\n", $i + 1, $movieCount));

                $this->processMovie();
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

    /**
     * @throws Exception
     */
    private function processMovie(): void
    {
        $postItem = $this->getPostItem(
            $this->getMovie()
        );

        $message = "Dispatching %s event for Movie: $postItem->title";

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
        $this->line('Find a movie');

        if ($this->currentChecks >= $this->maxChecks) {
            $this->error('findUnusedMovie ran too many times');

            throw new \RuntimeException('No Movie found');
        }

        if (count($this->usedMovies) >= $total) {
            $message = 'All movies have been used';
            $this->error($message);

            throw new \RuntimeException($message);
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

        $this->line("Found {$movie['Name']}");

        $this->usedMovies[] = $movie['Id'];

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
            ->append("\n\n");

        if (! empty($movie['Taglines'])) {
            $content = $content->append($movie['Taglines'][0])
                ->append("\n\n");
        }

        $content = $content->append($movie['Overview'])
            ->append("\n\n");

        $content = $content->append("Released: {$movie['ProductionYear']}")
            ->append("\n");

        if (! empty($movie['CriticRating'])) {
            $content = $content->append("Critics: 🍅 {$movie['CriticRating']}%")
                ->append("\n");
        }

        if (! empty($movie['OfficialRating'])) {
            $content = $content->append("Rated: {$movie['OfficialRating']}")
                ->append("\n");
        }

        if (! empty($movie['RunTimeTicks'])) {
            $content = $content->append(
                'Runtime: '.$this->convertRunTime((int) $movie['RunTimeTicks'])
            )
            ->append("\n");
        }

        if (! empty($movie['People'])) {
            $content = $content->append("\n\n");

            $director = $this->getDirector($movie['People']);
            if ($director) {
                $content = $content->append("Directed by: {$director}")
                    ->append("\n");
            }

            $cast = $this->getCastMembers($movie['People']);
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

            $cast .= "[{$actor['Name']}]("
                . sprintf(Config::string('emby-api.item_url'), $actor['Id'])
                . ")\n";
        }

        return $cast;
    }

    /**
     * parseMedia Method.
     *
     * @param array<string, string> $movie
     * @return Collection<RemoteImageItem>
     * @throws Exception
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
     * @throws Exception
     */
    private function processTrailer(array $movie): void
    {
        $item = new ProcessMediaItem(
            $movie['Id'],
            $movie['Name'],
            $movie['Path'] ?? '',
            $movie['RemoteTrailers'] ?? [],
        );

        $this->postUpdateItem = MovieTrailerFactory::create($item)
            ->setCurrentMovieIndex($this->currentMovieIndex)
            ->setQueueable($this->queueable)
            ->setToScreen($this->toScreen)
            ->process();
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
            foreach ($movie['TagItems'] as $tag) {
                $tags->add($tag['Name']);
            }
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
