<?php

return [

    'endpoint' => 'https://riddles-api-eight.vercel.app/%s/%s',

    'categories' => [
        'science',
        'funny',
        'math',
        'mystery',
        'logic',
    ],

    'max_items' => env('MAX_RIDDLE_ITEMS', 25),

];
