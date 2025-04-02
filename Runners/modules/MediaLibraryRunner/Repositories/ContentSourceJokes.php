<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Modules\MediaLibraryRunner\Models\Contents\ContentItem;
use Modules\MediaLibraryRunner\Models\Contents\ContentModel;
use Modules\MediaLibraryRunner\Models\Contents\Joke;

class ContentSourceJokes extends BaseContentSource
{
    public function getName(): string
    {
        return 'joke';
    }

    public function getModel(): ContentModel
    {
        return new Joke;
    }

    public function getTitle(ContentItem $content): string
    {
        $title = $this->cleanString($content->title);
        if ($title->length() > 40) {
            return Str::of($title->toString())
                ->words(6)
                ->toString();
        }

        return $title->toString();
    }

    public function getContent(ContentItem $content): string
    {
        $jokeBase = $this->cleanString($content->body);
        $title = $this->cleanString($content->title);

        $body = $title->length() > 40 && ! $jokeBase->lower()->contains($title->lower()->toString())
            ? $title->append("\n\n")->append($content->body)->toString()
            : $content->body;

        $category = sprintf(
            '**Category:** *%s*',
            $content->category,
        );

        return $body."\n\n".$category;
    }

    private function cleanString(string $value): Stringable
    {
        return Str::of($value)
            ->replace(['!', '?', '"', "'"], '')
            ->trim();
    }
}
