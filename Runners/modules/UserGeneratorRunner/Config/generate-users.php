<?php

return [

    'horizon_queue' => env('GENERATE_USERS_HORIZON_QUEUE', 'genusers'),

    'max_empty_runs' => (int) env('GENERATE_USERS_MAX_EMPTY_RUN', 5),

];
