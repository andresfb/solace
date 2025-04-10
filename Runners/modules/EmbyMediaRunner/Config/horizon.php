<?php

return [

    'emby-media-runner-supervisor' => [
        'connection' => 'horizon',
        'queue' => ['emby-media'],
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
