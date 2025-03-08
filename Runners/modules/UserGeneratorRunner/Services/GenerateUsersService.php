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
        // TODO: loop through the Users and raise an event so the Host can pick it up with a listener
    }
}
