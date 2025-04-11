<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Stringable;
use Modules\Common\Services\PostExistsService;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Factories\MovieTrailerFactory;

class GenerateSeriesPostService extends BaseGenerateMediaPostService
{
    public function __construct(
        PostExistsService $postExistsService,
        DownloadTrailerService $downloadTrailerService,
        EncodeTrailerService $encodeTrailerService
    ) {
        parent::__construct(
            $postExistsService,
            $downloadTrailerService,
            $encodeTrailerService
        );

        $this->mediaType = 'Series';
        $this->mediaIndex = Config::string('meilisearch.series_index');
        $this->maxChecks = Config::integer('generate-series-post.max_series_checks');
    }

    public function getPostLimit(): int
    {
        return Config::integer('generate-series-post.posts_limit');
    }

    protected function getTaskName(): string
    {
        return $this->GENERATE_SERIES_POST;
    }

    protected function getMediaTypeIcon(Stringable $content): Stringable
    {
        return $content->append('📺');
    }

    protected function getTypeBasedContent(array $item, Stringable $content): Stringable
    {
        if (! empty($item['Seasons'])) {
            $content = $content->append(
                sprintf('Seasons: %d', count($item['Seasons']))
            )
            ->append("\n");
        }

        if (! empty($item['Episodes'])) {
            $content = $content->append(
                sprintf('Episodes: %d', count($item['Episodes']))
            )->append("\n");
        }

        $status = 'Ongoing';
        if (! empty($item['EndDate'])) {
            $status = 'Finished';
        }

        return $content->append("Status: $status")
            ->append("\n");
    }

    protected function getRunTime(array $item, Stringable $content): Stringable
    {
        if (empty($item['Episodes'])) {
            return $content;
        }

        $totalTicks = 0;
        $count = count($item['Episodes']);

        foreach ($item['Episodes'] as $episode) {
            $totalTicks += (int) $episode['RunTimeTicks'];
        }

        $average = (int) floor($totalTicks / $count);

        return $content->append(
            'Average Episode Runtime: '.$this->convertRunTime($average)
        )
        ->append("\n");
    }

    /**
     * @throws Exception
     */
    protected function processTrailer(array $item): void
    {
        $path = '';
        if (! empty($item['Episodes'])) {
            $episode = $item['Episodes'][0];
            $path = $episode['Path'];
        }

        $mediaItem = new ProcessMediaItem(
            movieId: $item['Id'],
            name: $item['Name'],
            filePath: $path,
            trailerUrls: $item['RemoteTrailers'] ?? [],
        );

        $this->postUpdateItem = MovieTrailerFactory::create($mediaItem)
            ->setCurrentMovieIndex($this->currentItemIndex)
            ->setQueueable($this->queueable)
            ->setToScreen($this->toScreen)
            ->process();
    }
}
