<?php

declare(strict_types=1);

namespace Modules\EmbyMediaRunner\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Stringable;
use Modules\Common\Services\PostExistsService;
use Modules\EmbyMediaRunner\Dtos\ProcessMediaItem;
use Modules\EmbyMediaRunner\Factories\MovieTrailerFactory;

final class GenerateMoviePostService extends BaseGenerateMediaPostService
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

        $this->mediaType = 'Movie';
        $this->mediaIndex = Config::string('meilisearch.movies_index');
        $this->maxChecks = Config::integer('generate-movie-post.max_movie_checks');
    }

    public function getPostLimit(): int
    {
        return Config::integer('generate-movie-post.posts_limit');
    }

    protected function getTaskName(): string
    {
        return $this->GENERATE_MOVIE_POST;
    }

    protected function getMediaTypeIcon(Stringable $content): Stringable
    {
        return $content->append('🎬');
    }

    protected function getTypeBasedContent(array $item, Stringable $content): Stringable
    {
        return $content;
    }

    protected function getRunTime(array $item, Stringable $content): Stringable
    {
        if (! empty($item['RunTimeTicks'])) {
            return $content;
        }

        return $content->append(
            'Runtime: '.$this->convertRunTime((int) $item['RunTimeTicks'])
        )
        ->append("\n");
    }

    /**
     * @param array<string, string> $item
     * @throws Exception
     */
    protected function processTrailer(array $item): void
    {
        $mediaItem = new ProcessMediaItem(
            $item['Id'],
            $item['Name'],
            $item['Path'] ?? '',
            $item['RemoteTrailers'] ?? [],
        );

        $this->postUpdateItem = MovieTrailerFactory::create($mediaItem)
            ->setCurrentMovieIndex($this->currentItemIndex)
            ->setQueueable($this->queueable)
            ->setToScreen($this->toScreen)
            ->process();
    }
}
