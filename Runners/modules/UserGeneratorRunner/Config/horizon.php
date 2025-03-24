<?php

return [

    'gen-users-supervisor' => [
        'connection' => 'horizon',
        'queue' => ['genusers'],
        'memory' => 124,
        'autoScalingStrategy' => 'size',
        'minProcesses' => 1,
        'maxProcesses' => 3,
        'balanceMaxShift' => 1,
        'balanceCooldown' => 3,
        'timeout' => 180, // 3 minutes
        'tries' => 1,
    ],

];
