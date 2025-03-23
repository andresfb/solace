<?php

declare(strict_types=1);

namespace Modules\ApiConsumers\Traits;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

trait MakesHttpRequests
{
    protected string $baseUrl = '';

    private int $timeout = 0;

    /**
     * @return string[]
     */
    protected function getHeaders(): array
    {
        $customHeaders = config('ollama-laravel.headers', []);

        $authHeaders = $this->getAuthenticationHeaders();

        return array_merge($authHeaders, $customHeaders);
    }

    /**
     * @return string[]
     */
    protected function getAuthenticationHeaders(): array
    {
        $authType = config('ollama-laravel.auth.type');

        return match ($authType) {
            'bearer' => $this->getBearerHeader(),
            'basic' => $this->getBasicHeader(),
            default => [],
        };
    }

    /**
     * @return string[]
     */
    protected function getBearerHeader(): array
    {
        $token = config('ollama-laravel.auth.token');
        if (! $token) {
            throw new InvalidArgumentException('Bearer token is required when using token authentication');
        }

        return ['Authorization' => 'Bearer '.$token];
    }

    /**
     * @return string[]
     */
    protected function getBasicHeader(): array
    {
        $username = config('ollama-laravel.auth.username');
        $password = config('ollama-laravel.auth.password');
        if (! $username || ! $password) {
            throw new InvalidArgumentException('Username and password are required when using basic authentication');
        }
        $credentials = base64_encode($username.':'.$password);

        return ['Authorization' => 'Basic '.$credentials];
    }

    /**
     * sendRequest Method.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ConnectionException
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post'): mixed
    {
        $url = $this->baseUrl.$urlSuffix;
        $headers = $this->getHeaders();

        $http = Http::withHeaders($headers)
            ->timeout($this->timeout)
            ->throw()
            ->withOptions([
                'verify' => config('ollama-laravel.connection.verify_ssl', true),
            ]);

        if ($method === 'post') {
            return $http->post($url, $data)->json();
        }

        return $http->get($url, $data)->json();
    }
}
