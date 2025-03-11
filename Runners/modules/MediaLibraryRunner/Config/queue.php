<?php

return [

    'media-queue-runner' => [
        'driver' => 'redis',
        'connection' => env('MEDIA_RUNNER_REDIS_CNN', 'media-runner-cnn'),
        'queue' => 'fedex',
        'retry_after' => 2000,
        'block_for' => 5,
    ],

];
