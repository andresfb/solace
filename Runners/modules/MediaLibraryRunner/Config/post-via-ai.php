<?php

return [

    'posts_limit' => (int) env('MIGRATE_VIA_AI_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_VIA_AI_MAX_EMPTY_RUN', 10),

    'queue_connection' => env('MIGRATE_VIA_AI_REDIS_CNN', 'via-ai-cnn'),

    'queues' => env('MIGRATE_VIA_AI_QUEUES', 'skynet'),

    'ai_vision_model' => env('MIGRATE_VIA_AI_VISION_MODEL', 'llama3.2-vision'),

    'ai_post_prompt_content' => env(
        'MIGRATE_VIA_AI_POST_PROMPT_CONTENT',
        'Can you provide at least two paragraphs of text and 2 to 6 hashtags for a social'
        . ' media post from this picture. Please respond with the content only; do not'
        . ' add any extra options or comments and do not repeate the hashtags in the text.'
    ),

];
