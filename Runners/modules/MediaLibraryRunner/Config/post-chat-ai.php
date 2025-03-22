<?php

return [

    'posts_limit' => (int) env('MIGRATE_CHAT_AI_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_CHAT_AI_MAX_EMPTY_RUN', 10),

    'queue_connection' => env('MIGRATE_AI_REDIS_CNN', 'via-ai-cnn'),

    'queues' => env('MIGRATE_CHAT_AI_QUEUES', 'skynet'),

    'ai_model' => env('MIGRATE_CHAT_AI_MODEL', 'llama3.2-vision'),

    'ai_api_url' => env('MIGRATE_CHAT_AI_API_URL', 'http://127.0.0.1:11434'),

    'ai_temperature' => (float) env('MIGRATE_CHAT_AI_TEMPERATURE', 0.8),

    'ai_agent' => env('MIGRATE_CHAT_AI_AGENT', 'You are a savvy Social Media expert with a knack for creative content text'),

    'ai_post_prompt_content' => env(
        'MIGRATE_CHAT_AI_POST_PROMPT_CONTENT',
        "Please provide the content for one Social Media post with at least two paragraphs of text for an %s. You must include 2 to 6 hashtags. Come up with something creative, but don't include comments as part of the persona. Respond with the content only, and do not add any extra options or duplicate the hashtags in the text. Refrain from making the text about the promotion of a product or a service; make it sound natural and organic like it comes from a regular individual. Make it sound %s."
    ),

];
