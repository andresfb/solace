<?php

return [

    'posts_limit' => (int) env('MIGRATE_FULFILLED_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_FULFILLED_MAX_EMPTY_RUN', 15),

];
