<?php

declare(strict_types=1);

namespace Modules\UserGeneratorRunner\Services;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Modules\Common\Dtos\RandomUserItem;
use Modules\Common\Traits\Screenable;

class RandomUserService
{
    use Screenable;

    public function __construct(private readonly RandomImageService $imageService) {}

    /**
     * @throws Exception
     */
    public function execute(): Collection
    {
        $url = sprintf(
            config('random_user.api_url'),
            config('user_generator.max_new_users')
        );

        $this->line('Calling the randomuser.me API '.now());

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
            ->timeout(120)
            ->get($url);

        $this->line(sprintf("Api call finished at %s\n", now()));

        if ($response->failed()) {
            $this->error('Error found '.$response->body());

            throw new \RuntimeException($response->body());
        }

        $data = $response->json();

        $this->line("Parsing data...\n");

        return $this->parseResponse(collect($data['results']));
    }

    private function parseResponse(Collection $response): Collection
    {
        return $response->map(fn (array $user): RandomUserItem => new RandomUserItem(
            gender: $user['gender'],
            name: sprintf('%s %s', $user['name']['first'], $user['name']['last']),
            email: $user['email'],
            password: $user['login']['sha256'],
            phone: $user['cell'] ?? $user['phone'],
            city: $user['location']['city'],
            country: $user['location']['country'],
            picture: $this->imageService->getImage($user['picture']['large']),
            dob: CarbonImmutable::parse($user['dob']['date']),
            registered: CarbonImmutable::parse($user['registered']['date']),
        ));
    }
}
