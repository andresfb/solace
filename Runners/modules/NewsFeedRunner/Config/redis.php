<?php

return [

    'news-feed-runner-cnn' => [
        'url' => env('NEWS_FEED_RUNNER_REDIS_URL'),
        'host' => env('NEWS_FEED_RUNNER_REDIS_HOST', '127.0.0.1'),
        'password' => env('NEWS_FEED_RUNNER_REDIS_PASSWORD'),
        'port' => env('NEWS_FEED_RUNNER_REDIS_PORT', '6379'),
        'database' => env('NEWS_FEED_RUNNER_REDIS_DATABASE', '0'),
    ],

    'quoted-article-runner-cnn' => [
        'url' => env('QUOTED_ARTICLE_RUNNER_REDIS_URL'),
        'host' => env('QUOTED_ARTICLE_RUNNER_REDIS_HOST', '127.0.0.1'),
        'password' => env('QUOTED_ARTICLE_RUNNER_REDIS_PASSWORD'),
        'port' => env('QUOTED_ARTICLE_RUNNER_REDIS_PORT', '6379'),
        'database' => env('QUOTED_ARTICLE_RUNNER_REDIS_DATABASE', '0'),
    ],

];
