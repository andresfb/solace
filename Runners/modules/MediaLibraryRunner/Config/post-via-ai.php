<?php

return [

    'posts_limit' => (int) env('MIGRATE_VIA_AI_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_VIA_AI_MAX_EMPTY_RUN', 10),

    'ai_readable_file_path' => env('MIGRATE_VIA_AI_READABLE_FILE_PATH', '/Volumes/%s'),

    'ai_vision_model' => env('MIGRATE_VIA_AI_VISION_MODEL', 'llava:13b'),

    'ai_post_prompt_title' => env('MIGRATE_VIA_AI_POST_PROMPT_TITLE', 'can you provide a title for a social media post from this picture'),

    'ai_post_prompt_content' => env('MIGRATE_VIA_AI_POST_PROMPT_CONTENT', 'can you provide at least two paragraphs of text and up to 6 hashtags for a social media post from this picture'),

];
