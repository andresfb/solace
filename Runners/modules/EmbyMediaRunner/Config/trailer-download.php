<?php

return [

    'queue_connection' => env('EMBY_MEDIA_DOWNLOAD_REDIS_CNN', 'downloader-cnn'),

    'queues' => env('EMBY_MEDIA_DOWNLOAD_QUEUE', 'napster'),

];
