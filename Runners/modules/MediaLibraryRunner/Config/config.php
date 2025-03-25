<?php

return [

    'banded_tags' => explode(',', (string) env('MEDIA_RUNNER_BANDED_TAGS', 'bible,quran')),

    'media_path' => env('MEDIA_RUNNER_STORAGE_PATH'),

    'queue_connection' => env('MEDIA_RUNNER_REDIS_CNN', 'media-runner-cnn'),

    'queues' => env('MEDIA_RUNNER_QUEUES', 'fedex,usps,dhl,ups'),

    'horizon_queue' => env('MEDIA_RUNNER_HORIZON_QUEUE', 'media-runner'),

    'ai_sparks' => explode(',', (string) env('MIGRATE_AI_SPARKS')),

    'ai_hashtags_prompt' => env(
        'MIGRATE_AI_HASHTAGS_PROMPT',
        'Can you please generate a list of 2 to 6 hashtags from the given text? Please give me the hashtags only; do not add any extra text or comments. If the hashtags are made up of several words, they should be title-cased and have no spaces between them. For example, #ThisIsAnExample.'
    ),

];
