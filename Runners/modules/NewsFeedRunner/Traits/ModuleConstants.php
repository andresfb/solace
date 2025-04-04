<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Traits;

trait ModuleConstants
{
    public string $MODULE_NAME = 'news_feed_runner';

    public string $NEWS_FEED = 'news_feed';

    public string $IMPORT_IMAGED_ARTICLES = 'imaged-article-importer';

    public string $IMPORT_PICSUM_ARTICLE = 'picsum-article-importer';

    public string $IMPORT_AI_ARTICLE = 'ai-article-importer';

    public string $IMPORT_QUOTED_ARTICLE = 'quoted-article-importer';
}
