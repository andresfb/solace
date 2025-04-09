<?php

return [

    'queue_connection' => env('EMBY_MEDIA_ENCODER_REDIS_CNN', 'encoder-cnn'),

    'queues' => env('EMBY_MEDIA_ENCODER_QUEUE', 'bentley'),

    'clip-length' => (float) env('EMBY_MEDIA_ENCODER_CLIP_LENGTH', 9.0),

    'transition-duration' => (float) env('EMBY_MEDIA_ENCODER_TRANSITION_DURATION', 0.6),

    'max-trailer-length' => (float) env('EMBY_MEDIA_ENCODER_MAX_TRAILER_LENGTH', 120.0),

    'scale-factor' => (float) env('EMBY_MEDIA_ENCODER_SCALE_FACTOR', 0.04),

    'padding_time' => (float) env('EMBY_MEDIA_ENCODER_PADDING_TIME', 0.08),

];
