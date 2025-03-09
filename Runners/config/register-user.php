<?php

return [

    'queue_connection' => env('REGISTER_USERS_QUEUE_CNN', 'reg-users-cnn'),

    'queues' => env('REGISTER_USERS_QUEUES', 'pudicitia,operatio'),

];
