<?php

return [

    'news-feed-runner-supervisor' => [
        'connection' => 'horizon',
        'queue' => ['news-feed'],
        'memory' => 124,
        'autoScalingStrategy' => 'size',
        'minProcesses' => 1,
        'maxProcesses' => 5,
        'balanceMaxShift' => 1,
        'balanceCooldown' => 3,
        'timeout' => 300, // 5 minutes
        'tries' => 1,
    ],

];
