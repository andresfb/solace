<?php

namespace Modules\UserGeneratorRunner\Dtos;

use Carbon\Carbon;

readonly class RandomUserItem
{
    public function __construct(
        public string $gender,
        public string $name,
        public string $email,
        public string $password,
        public string $phone,
        public string $city,
        public string $picture,
        public Carbon $dob,
        public Carbon $registered,
    ) {}
}
