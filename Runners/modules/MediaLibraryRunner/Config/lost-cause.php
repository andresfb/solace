<?php

return [

    'posts_limit' => (int) env('MIGRATE_LOST_CAUSE_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_LOST_CAUSE_MAX_EMPTY_RUN', 100),

    'search_url' => env('MIGRATE_LOST_CAUSE_SEARCH_URL', 'https://start.duckduckgo.com/?q='),

];
