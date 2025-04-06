<?php

return [

    'trailer-download-queue-runner' => [
        'driver' => 'redis',
        'connection' => env('EMBY_MEDIA_DOWNLOAD_REDIS_CNN', 'downloader-cnn'),
        'queue' => 'napster',
        'retry_after' => 2000,
        'block_for' => 5,
    ],

    'encode-trailer-queue-runner' => [
        'driver' => 'redis',
        'connection' => env('EMBY_MEDIA_ENCODER_REDIS_CNN', 'encoder-cnn'),
        'queue' => 'bentley',
        'retry_after' => 2000,
        'block_for' => 5,
    ],

];
