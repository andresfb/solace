<?php

return [

    'task_enabled' => (bool) env('MIGRATE_FULFILLED_POSTS_ENABLED', false),

    'posts_limit' => (int) env('MIGRATE_FULFILLED_POSTS_LIMIT', 100),

];
