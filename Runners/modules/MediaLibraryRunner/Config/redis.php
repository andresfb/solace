<?php

return [

    'media-runner-cnn' => [
        'url' => env('MEDIA_RUNNER_REDIS_URL'),
        'host' => env('MEDIA_RUNNER_REDIS_HOST', '127.0.0.1'),
        'password' => env('MEDIA_RUNNER_REDIS_PASSWORD'),
        'port' => env('MEDIA_RUNNER_REDIS_PORT', '6379'),
        'database' => env('MEDIA_RUNNER_REDIS_DATABASE', '0'),
    ],

    'ai-runner-cnn' => [
        'url' => env('MIGRATE_VIA_AI_REDIS_URL'),
        'host' => env('MIGRATE_VIA_AI_REDIS_HOST', '127.0.0.1'),
        'password' => env('MIGRATE_VIA_AI_REDIS_PASSWORD'),
        'port' => env('MIGRATE_VIA_AI_REDIS_PORT', '6379'),
        'database' => env('MIGRATE_VIA_AI_REDIS_DATABASE', '1'),
    ],

];
