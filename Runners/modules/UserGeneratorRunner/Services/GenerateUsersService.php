<?php

namespace Modules\UserGeneratorRunner\Services;

use App\Traits\Screenable;
use Exception;

class GenerateUsersService
{
    use Screenable;

    public function __construct(private readonly RandomUserService $service)
    {
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $this->info('Generating users...');

        $users = $this->service->execute();
    }
}
