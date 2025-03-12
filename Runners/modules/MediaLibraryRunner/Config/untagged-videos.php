<?php

return [

    'posts_limit' => (int) env('MIGRATE_UNTAGGED_VIDEO_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_UNTAGGED_VIDEO_MAX_EMPTY_RUN', 10),

];
