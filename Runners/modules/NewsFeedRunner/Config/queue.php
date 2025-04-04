<?php

return [

    'news-feed-queue-runner' => [
        'driver' => 'redis',
        'connection' => env('NEWS_FEED_RUNNER_REDIS_CNN', 'news-feed-runner-cnn'),
        'queue' => 'daily-planet',
        'retry_after' => 2000,
        'block_for' => 5,
    ],

    'quoted-article-queue-runner' => [
        'driver' => 'redis',
        'connection' => env('IMPORT_QUOTED_ARTICLES_REDIS_CNN', 'quoted-article-runner-cnn'),
        'queue' => 'webster',
        'retry_after' => 2000,
        'block_for' => 5,
    ],

];
