<?php

return [

    'queue_connection' => env('POSTS_RUNNER_QUEUE_CNN', 'posts-runner'),

    'queues' => env('POSTS_RUNNER_QUEUES', 'calliope,clio,erato,melpomene'),

];
