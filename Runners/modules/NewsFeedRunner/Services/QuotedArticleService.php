<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Modules\Common\Enum\RunnerStatus;
use Modules\Common\Events\ChangeStatusEvent;
use Modules\Common\Traits\QueueSelectable;
use Modules\Common\Traits\Screenable;
use Modules\Common\Traits\SendToQueue;
use Modules\NewsFeedRunner\Jobs\ArticleJob;
use Modules\NewsFeedRunner\Models\Articles\Article;
use Modules\NewsFeedRunner\Traits\ModuleConstants;

class QuotedArticleService
{
    use ModuleConstants;
    use QueueSelectable;
    use Screenable;
    use SendToQueue;

    public function __construct(private readonly ArticleService $articleService) {}

    public function execute(Article $article): void
    {
        try {
            $image = $this->generateImage($article);
        } catch (Exception) {
            ChangeStatusEvent::dispatch(
                $this->NEWS_FEED,
                $article->id,
                RunnerStatus::UNUSABLE,
            );

            return;
        }

        $this->line("Saving image $image to article");

        Article::where('id', $article->id)
            ->update([
                'thumbnail' => $image,
            ]);

        if ($this->queueable) {
            $this->line('Dispatching ArticleJob');

            ArticleJob::dispatch($article->id, $this->IMPORT_QUOTED_ARTICLE)
                ->onConnection($this->getConnection($this->NEWS_FEED))
                ->onQueue($this->getQueue($this->NEWS_FEED))
                ->delay(now()->addSeconds(5));

            return;
        }

        $this->line('Executing ArticleService...');

        $updatedArticle = $article->where('id', $article->id)
            ->firstOrFail();

        $this->articleService->setToScreen($this->toScreen)
            ->setQueueable(false)
            ->execute(
                $updatedArticle,
                $this->IMPORT_QUOTED_ARTICLE
            );
    }

    private function generateImage(Article $article): string
    {
        $text = str($article->parseContent())
            ->trim()
            ->replace("\n", ' ')
            ->replace("\r", ' ')
            ->replace("\t", ' ')
            ->replace('<br>', ' ')
            ->replace('<br />', ' ')
            ->replace('    ', ' ')
            ->replace('   ', ' ')
            ->replace('  ', ' ')
            ->replace('—', "\n\n—")
            ->trim()
            ->value();

        $tempPath = md5("$article->id:$article->feed_id");
        $processPath = Storage::disk('processing')->path($tempPath);

        if (! file_exists($processPath) && ! mkdir($processPath, 0775, true) && ! is_dir($processPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $processPath));
        }

        $imageFile = "$processPath/$article->id.png";

        $imageWidth = 1080;
        $imageHeight = 1080;
        $fontSize = 56;
        $lineHeightMultiplier = 1.5;
        $fontPath = config('quoted-article-importer.font_path');

        $maxTextWidth = $imageWidth * 0.9; // Keep some padding
        $wrappedLines = $this->wrapTextToFit($text, $fontPath, $fontSize, $maxTextWidth);

        [$y, $lineHeights] = $this->getMultiLineCenteredText(
            $wrappedLines,
            $fontPath,
            $fontSize,
            $imageHeight,
            $lineHeightMultiplier
        );

        // Create the manager
        $manager = new ImageManager(new Driver);

        // Create image
        $image = $manager->create($imageWidth, $imageHeight)
            ->fill('#fafafa');

        // Draw each line manually
        $yOffset = $y;
        foreach ($wrappedLines as $index => $line) {
            $image->text($line, $imageWidth / 2, (int) $yOffset, function ($font) use ($fontPath, $fontSize): void {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color('#16151c');
                $font->align('center');
                $font->valign('top');
            });

            $yOffset += $lineHeights[$index];
        }

        // Save or output
        $image->save($imageFile);

        if (! file_exists($imageFile)) {
            throw new \RuntimeException("Could not create image for $article->id: $imageFile");
        }

        return $imageFile;
    }

    /**
     * @return array<string>
     */
    private function wrapTextToFit(string $text, string $fontPath, int $fontSize, float $maxWidth): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine === '' ? $word : $currentLine.' '.$word;
            $box = imagettfbbox($fontSize, 0, $fontPath, $testLine);
            if (! $box) {
                continue;
            }

            $textWidth = abs($box[2] - $box[0]);

            if ($textWidth > $maxWidth) {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    /**
     * @param array<string> $lines
     * @return array{float|int, list<float>}
     */
    private function getMultiLineCenteredText(
        array $lines,
        string $fontPath,
        int $fontSize,
        int $imageHeight,
        float $lineHeightMultiplier = 1.6): array
    {
        $totalHeight = 0;
        $lineHeights = [];

        foreach ($lines as $line) {
            $box = imagettfbbox($fontSize, 0, $fontPath, $line);
            if (! $box) {
                continue;
            }

            $lineHeight = abs($box[7] - $box[1]) * $lineHeightMultiplier;
            $lineHeights[] = $lineHeight;
            $totalHeight += $lineHeight;
        }

        $y = ($imageHeight - $totalHeight) / 2;

        return [$y, $lineHeights];
    }
}
