<?php

return [

    'random_users_limit' => (int) env('RANDOM_USERS_LIMIT', 1000),

    'profile_image_sizes' => explode(',', env('PROFILE_IMAGE_SIZES', '75,128')),

];
