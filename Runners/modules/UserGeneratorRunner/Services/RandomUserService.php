<?php

namespace Modules\UserGeneratorRunner\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Modules\UserGeneratorRunner\Dtos\RandomUserItem;

readonly class RandomUserService
{
    public function __construct(private RandomImageService $imageService) {}

    /**
     * @throws Exception
     */
    public function execute(): Collection
    {
        $url = sprintf(
            config('random_user.api_url'),
            config('user_generator.max_new_users')
        );

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get($url);

        if ($response->failed()) {
            throw new \RuntimeException($response->body());
        }

        $data = $response->json();

        return $this->parseResponse(collect($data['results']));
    }

    private function parseResponse(Collection $response): Collection
    {
        return $response->map(function (array $user) {
            return new RandomUserItem(
                gender: $user['gender'],
                name: sprintf("%s %s", $user['name']['first'], $user['name']['last']),
                email: $user['email'],
                password: $user['login']['sha256'],
                phone: $user['cell'] ?? $user['phone'],
                city: $user['location']['city'],
                picture: $this->imageService->getImage($user['picture']['large']),
                dob: Carbon::parse($user['dob']['date']),
                registered: Carbon::parse($user['registered']['date']),
            );
        });
    }
}
