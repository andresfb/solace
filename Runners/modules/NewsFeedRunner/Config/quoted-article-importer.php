<?php

return [

    'posts_limit' => (int) env('IMPORT_QUOTED_ARTICLES_POSTS_LIMIT', 4),

    'max_empty_runs' => (int) env('IMPORT_QUOTED_ARTICLES_MAX_EMPTY_RUN', 10),

    'font_path' => env('IMPORT_QUOTED_ARTICLES_FONT_PATH', '/usr/share/fonts/truetype/firacode/FiraCode-Regular.ttf'),

    'queue_connection' => env('IMPORT_QUOTED_ARTICLES_REDIS_CNN', 'quoted-article-runner-cnn'),

    'queues' => env('IMPORT_QUOTED_ARTICLES_QUEUES', 'webster'),

];
