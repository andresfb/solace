<?php

declare(strict_types=1);

namespace Modules\ApiConsumers\Services;

use Illuminate\Http\Client\ConnectionException;
use Modules\ApiConsumers\Traits\MakesHttpRequests;

class Ollama
{
    use MakesHttpRequests;

    private string $model;

    private string $prompt = '';

    private string $format = '';

    /** @var array<string, mixed> */
    private array $options = [];

    private bool $raw = false;

    private string $agent = '';

    private string $image = '';

    /** @var array<string> */
    private array $images = [];

    private string $keepAlive = '5m';

    public function __construct()
    {
        $this->baseUrl = config('ollama-laravel.url');
        $this->model = config('ollama-laravel.model');
        $this->timeout = config('ollama-laravel.connection.timeout');
    }

    public function url(string $url): static
    {
        $this->baseUrl = $url;

        return $this;
    }

    public function agent(string $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function prompt(string $prompt): static
    {
        $this->prompt = $prompt;

        return $this;
    }

    public function model(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return $this
     */
    public function options(array $options = []): static
    {
        $this->options = $options;

        return $this;
    }

    public function raw(bool $raw): static
    {
        $this->raw = $raw;

        return $this;
    }

    public function keepAlive(string $keepAlive): static
    {
        $this->keepAlive = $keepAlive;

        return $this;
    }

    public function setTimeout(int $timeout): Ollama
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function image(string $imagePath): static
    {
        if (! file_exists($imagePath)) {
            throw new \RuntimeException("Image file does not exist: $imagePath");
        }

        $contents = file_get_contents($imagePath);
        if ($contents === false) {
            throw new \RuntimeException("Image file could not be read: $imagePath");
        }

        $this->image = base64_encode($contents);

        return $this;
    }

    /**
     * @param  array<string>  $imagePaths
     * @return $this
     */
    public function images(array $imagePaths): static
    {
        foreach ($imagePaths as $imagePath) {
            if (! file_exists($imagePath)) {
                throw new \RuntimeException("Image file does not exist: $imagePath");
            }

            $contents = file_get_contents($imagePath);
            if ($contents === false) {
                throw new \RuntimeException("Image file could not be read: $imagePath");
            }

            $this->images[] = base64_encode($contents);
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     */
    public function ask(): array
    {
        $requestData = [
            'stream' => false,
            'model' => $this->model,
            'system' => $this->agent,
            'prompt' => $this->prompt,
            'format' => $this->format,
            'options' => $this->options,
            'raw' => $this->raw,
            'keep_alive' => $this->keepAlive,
        ];

        if ($this->image !== '' && $this->image !== '0') {
            $requestData['images'] = [$this->image];
        }

        if ($this->images !== []) {
            $requestData['images'] = $this->images;
        }

        $response = $this->sendRequest('/api/generate', $requestData);
        if ($response === null) {
            throw new \RuntimeException("Could not generate image response: $response");
        }

        return (array) $response;
    }
}
