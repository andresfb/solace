<?php

return [

    'media_path' => env('NEWS_FEED_RUNNER_STORAGE_PATH'),

    'queue_connection' => env('NEWS_FEED_RUNNER_REDIS_CNN', 'news-feed-runner-cnn'),

    'queues' => env('NEWS_FEED_RUNNER_QUEUES', 'daily-planet,daily-bugle'),

    'horizon_queue' => env('NEWS_FEED_RUNNER_HORIZON_QUEUE', 'news-feed-runner'),

    'quote-based-providers' => array_map('intval', explode(',', (string) env('NEWS_FEED_QUOTE_BASED_PROVIDERS', '13'))),

    'quote-based-feeds' => array_map('intval', explode(',', (string) env('NEWS_FEED_QUOTE_BASED_FEEDS', '23'))),

];
