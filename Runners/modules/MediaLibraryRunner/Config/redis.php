<?php

return [

    'fulfill-cnn' => [
        'url' => env('MEDIA_RUNNER_REDIS_URL'),
        'host' => env('MEDIA_RUNNER_REDIS_HOST', '127.0.0.1'),
        'password' => env('MEDIA_RUNNER_REDIS_PASSWORD'),
        'port' => env('MEDIA_RUNNER_REDIS_PORT', '6379'),
        'database' => env('MEDIA_RUNNER_REDIS_DATABASE', '1'),
    ],

];
