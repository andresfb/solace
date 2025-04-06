<?php

return [

    'downloader-cnn' => [
        'url' => env('TRAILER_DOWNLOAD_REDIS_URL'),
        'host' => env('TRAILER_DOWNLOAD_REDIS_HOST', '127.0.0.1'),
        'password' => env('TRAILER_DOWNLOAD_REDIS_PASSWORD'),
        'port' => env('TRAILER_DOWNLOAD_REDIS_PORT', '6379'),
        'database' => env('TRAILER_DOWNLOAD_REDIS_DATABASE', '0'),
    ],

    'encoder-cnn' => [
        'url' => env('TRAILER_ENCODER_REDIS_URL'),
        'host' => env('TRAILER_ENCODER_REDIS_HOST', '127.0.0.1'),
        'password' => env('TRAILER_ENCODER_REDIS_PASSWORD'),
        'port' => env('TRAILER_ENCODER_REDIS_PORT', '6379'),
        'database' => env('TRAILER_ENCODER_REDIS_DATABASE', '0'),
    ],

];
