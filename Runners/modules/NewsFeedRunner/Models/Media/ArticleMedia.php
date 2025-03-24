<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Models\Media;

use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Common\Models\Media;
use Modules\NewsFeedRunner\Models\Media\Scopes\MediaModelTypeScope;

class ArticleMedia extends Media
{
    use SoftDeletes;

    protected $table = 'media';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('database.news_feed_runner_connection'));
    }

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope(new MediaModelTypeScope);
    }

    protected function getMediaPath(): string
    {
        return config('news_feed_runner.media_path');
    }
}
