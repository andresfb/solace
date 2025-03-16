<?php

return [

    'media-queue-runner' => [
        'driver' => 'redis',
        'connection' => env('MEDIA_RUNNER_REDIS_CNN', 'media-runner-cnn'),
        'queue' => 'fedex',
        'retry_after' => 2000,
        'block_for' => 5,
    ],

    'ai-queue-runner' => [
        'driver' => 'redis',
        'connection' => env('MIGRATE_AI_REDIS_CNN', 'ai-runner-cnn'),
        'queue' => 'skynet',
        'retry_after' => 2000,
        'block_for' => 5,
    ],

];
