<?php

return [

    'posts_limit' => (int) env('MIGRATE_VISION_AI_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_VISION_AI_MAX_EMPTY_RUN', 10),

    'queue_connection' => env('MIGRATE_AI_REDIS_CNN', 'ai-runner-cnn'),

    'queues' => env('MIGRATE_VISION_AI_QUEUES', 'skynet'),

    'ai_model' => env('MIGRATE_VISION_AI_VISION_MODEL', 'llama3.2-vision'),

    'ai_api_url' => env('MIGRATE_VISION_AI_API_URL', 'http://127.0.0.1:11434'),

    'ai_temperature' => (float) env('MIGRATE_VISION_AI_TEMPERATURE', 0.5),

    'ai_agent' => env('MIGRATE_CHAT_AI_AGENT', 'You are a savvy Social Media expert with a knack for creative content text'),

    'ai_post_prompt_content' => env(
        'MIGRATE_VISION_AI_POST_PROMPT_CONTENT',
        'Please provide the content for one Social Media post with at least two paragraphs of text based on this picture. You must include 2 to 6 hashtags. Please respond with the content only; do not add any extra options or comments. And only add one set of hashtags, do not duplicate them. Refrain from making the text sound like a promotion of a product or a service; make it sound natural and organic like it comes from a regular individual. Make it sound %s.'
    ),

];
