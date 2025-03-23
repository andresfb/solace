<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    Modules\Common\Providers\CommonLibsServiceProvider::class,
    Modules\ApiConsumers\Providers\ApiConsumersServiceProvider::class,
    Modules\MediaLibraryRunner\Providers\MediaLibraryRunnerServiceProvider::class,
    Modules\UserGeneratorRunner\Providers\UserGeneratorRunnerServiceProvider::class,
];
