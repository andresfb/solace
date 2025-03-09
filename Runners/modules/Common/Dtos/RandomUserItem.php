<?php

namespace Modules\Common\Dtos;

use Carbon\CarbonImmutable;

readonly class RandomUserItem
{
    public function __construct(
        public string $gender,
        public string $name,
        public string $email,
        public string $password,
        public string $phone,
        public string $city,
        public string $country,
        public string $picture,
        public CarbonImmutable $dob,
        public CarbonImmutable $registered,
    ) {}
}
