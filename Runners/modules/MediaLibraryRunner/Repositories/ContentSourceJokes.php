<?php

declare(strict_types=1);

namespace Modules\MediaLibraryRunner\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Modules\MediaLibraryRunner\Interfaces\ContentSourceInterface;
use Modules\MediaLibraryRunner\Models\Content\BaseContentModel;
use Modules\MediaLibraryRunner\Models\Content\Joke;
use Modules\MediaLibraryRunner\Models\Post\LibraryPost;

class ContentSourceJokes implements ContentSourceInterface
{
    public function getName(): string
    {
        return 'joke';
    }

    public function getRandomContent(): ?BaseContentModel
    {
        return Joke::getRandom();
    }

    public function generateContent(LibraryPost $libraryPost, BaseContentModel $model): LibraryPost
    {
        $libraryPost->title = $this->getTitle($model);
        $libraryPost->content = $this->getContent($model);
        $libraryPost->source = "joke:$model->id";

        return $libraryPost;
    }

    public function updateSource(int $sourceId): void
    {
        Joke::where('id', $sourceId)->update([
            'used' => 1,
        ]);
    }

    public function getTitle(Joke $joke): string
    {
        $title = $this->cleanString($joke->title);
        if ($title->length() > 40) {
            return Str::of($title)
                ->words(6)
                ->toString();
        }

        return $title->toString();
    }

    public function getContent(Joke $joke): string
    {
        $jokeBase = $this->cleanString($joke->body);
        $title = $this->cleanString($joke->title);

        $body = $title->length() > 40 && !$jokeBase->lower()->contains($title->lower())
            ? $title->append("\n\n")->append($joke->body)->toString()
            : $joke->body;

        $category = sprintf(
            "**Category:** *%s*",
            $joke->category,
        );

        return $body . "\n\n" . $category;
    }

    private function cleanString(string $value): Stringable
    {
        return Str::of($value)
            ->replace(['!', '?', '"', "'"], '')
            ->trim();
    }
}
