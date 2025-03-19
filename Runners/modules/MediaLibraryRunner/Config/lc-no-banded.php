<?php

return [

    'posts_limit' => (int) env('MIGRATE_LC_NO_BANDED_POSTS_LIMIT', 100),

    'max_empty_runs' => (int) env('MIGRATE_LC_NO_BANDED_MAX_EMPTY_RUN', 100),

];
