<?php

return [

    'banded_tags' => explode(',', (string) env('MEDIA_RUNNER_BANDED_TAGS', 'bible,quran')),

    'media_path' => env('MEDIA_RUNNER_STORAGE_PATH'),

    'queue_connection' => env('MEDIA_RUNNER_REDIS_CNN', 'media-runner-cnn'),

    'queues' => env('MEDIA_RUNNER_QUEUES', 'fedex,usps,dhl,ups'),

    'horizon_queue' => env('MEDIA_RUNNER_HORIZON_QUEUE', 'media-runner'),

];
