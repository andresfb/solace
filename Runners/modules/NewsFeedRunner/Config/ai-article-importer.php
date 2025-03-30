<?php

return [

    'posts_limit' => (int) env('IMPORT_AI_ARTICLES_POSTS_LIMIT', 4),

    'max_empty_runs' => (int) env('IMPORT_AI_ARTICLES_MAX_EMPTY_RUN', 10),

    'queue_connection' => env('IMPORT_AI_ARTICLES_REDIS_CNN', 'ai-runner-cnn'),

    'queues' => env('IMPORT_AI_ARTICLES_QUEUES', 'skynet'),

    'model' => env('IMPORT_AI_ARTICLES_IMAGE_MODEL', 'dall-e-3'),

];
