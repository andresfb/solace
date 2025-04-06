<?php

return [

    'queue_connection' => env('EMBY_MEDIA_ENCODER_REDIS_CNN', 'encoder-cnn'),

    'queues' => env('EMBY_MEDIA_ENCODER_QUEUE', 'bentley'),

];
