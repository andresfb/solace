<?php

declare(strict_types=1);

namespace Modules\OllamaApi\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

trait MakesHttpRequests
{
    protected string $baseUrl = '';

    private int $timeout = 0;

    protected function getHeaders(): array
    {
        $customHeaders = config('ollama-laravel.headers', []);

        $authHeaders = $this->getAuthenticationHeaders();

        return array_merge($authHeaders, $customHeaders);
    }

    protected function getAuthenticationHeaders(): array
    {
        $authType = config('ollama-laravel.auth.type');

        return match ($authType) {
            'bearer' => $this->getBearerHeader(),
            'basic' => $this->getBasicHeader(),
            default => [],
        };
    }

    protected function getBearerHeader(): array
    {
        $token = config('ollama-laravel.auth.token');
        if (! $token) {
            throw new InvalidArgumentException('Bearer token is required when using token authentication');
        }

        return ['Authorization' => 'Bearer '.$token];
    }

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
     * @throws GuzzleException
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post'): null|array|Response
    {
        $url = config('ollama-laravel.url').$urlSuffix;
        $headers = $this->getHeaders();

        if (! empty($data['stream']) && $data['stream'] === true) {
            $client = new Client;
            $options = [
                'json' => $data,
                'stream' => true,
                'timeout' => $this->timeout,
                'headers' => $headers,
                'verify' => config('ollama-laravel.connection.verify_ssl', true),
            ];

            return $client->request($method, $url, $options);
        }

        $http = Http::withHeaders($headers)
            ->timeout($this->timeout)
            ->withOptions(['verify' => config('ollama-laravel.connection.verify_ssl', true)]);

        return $http->$method($url, $data)->json();
    }
}
