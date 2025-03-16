<?php

return [

    'posts_limit' => (int) env('MIGRATE_CHAT_AI_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_CHAT_AI_MAX_EMPTY_RUN', 10),

    'queue_connection' => env('MIGRATE_AI_REDIS_CNN', 'via-ai-cnn'),

    'queues' => env('MIGRATE_CHAT_AI_QUEUES', 'skynet'),

    'ai_model' => env('MIGRATE_CHAT_AI_MODEL', 'llama3.2-vision'),

    'ai_agent' => env('MIGRATE_CHAT_AI_AGENT', 'You are a savvy Social Media with a knack for creative content text'),

    'ai_post_prompt_content' => env(
        'MIGRATE_CHAT_AI_POST_PROMPT_CONTENT',
        "Please provide the content for one Social Media post with at least two paragraphs of text and 2 to 6 hashtags for an image. It would need to be random, as I don't know anything about the image. Please don't include any comments as part of the persona. Respond with the content only, and do not add any extra options or repeat the hashtags in the text."
    ),

];
