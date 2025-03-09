<?php

return [

    'task_enabled' => (bool) env('MIGRATE_FULFILLED_POSTS_ENABLED', false),

    'queue_connection' => env('MEDIA_RUNNER_REDIS_CNN', 'fulfill-cnn'),

    'queues' => env('MEDIA_RUNNER_QUEUES', 'fedex,usps'),

    'horizon_queue' => env('MEDIA_RUNNER_HORIZON_QUEUE', 'fulfiller'),

    'posts_limit' => (int) env('MIGRATE_FULFILLED_POSTS_LIMIT', 100),

];
