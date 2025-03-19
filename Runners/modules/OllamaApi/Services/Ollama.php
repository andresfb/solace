<?php

declare(strict_types=1);

namespace Modules\OllamaApi\Services;

use Closure;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Modules\OllamaApi\Traits\MakesHttpRequests;
use Modules\OllamaApi\Traits\StreamHelper;
use Psr\Http\Message\StreamInterface;

class Ollama
{
    use MakesHttpRequests;
    use StreamHelper;

    private string $model;

    private string $prompt = '';

    private string $format = '';

    private array $options = [];

    private array $tools = [];

    private bool $stream = false;

    private bool $raw = false;

    private string $agent = '';

    private ?string $image = null;

    private ?array $images = [];

    private string $keepAlive = "5m";

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

    public function options(array $options = []): static
    {
        $this->options = $options;
        return $this;
    }

    public function stream(bool $stream = false): static
    {
        $this->stream = $stream;
        return $this;
    }

    public function tools(array $tools = []): static
    {
        $this->tools = $tools;
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
        if (!file_exists($imagePath)) {
            throw new \RuntimeException("Image file does not exist: $imagePath");
        }

        $this->image = base64_encode(file_get_contents($imagePath));
        return $this;
    }

    public function images(array $imagePaths): static
    {
        foreach ($imagePaths as $imagePath) {
            if (!file_exists($imagePath)) {
                throw new \RuntimeException("Image file does not exist: $imagePath");
            }

            $this->images[] = base64_encode(file_get_contents($imagePath));
        }

        return $this;
    }

    /**
     * @throws GuzzleException
     */
    public function ask(): Response|array
    {
        $requestData = [
            'model' => $this->model,
            'system' => $this->agent,
            'prompt' => $this->prompt,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'raw' => $this->raw,
            'keep_alive' => $this->keepAlive,
        ];

        if ($this->image !== null && $this->image !== '' && $this->image !== '0') {
            $requestData['images'] = [$this->image];
        }

        if ($this->images !== null && $this->images !== []) {
            $requestData['images'] = $this->images;
        }

        return $this->sendRequest('/api/generate', $requestData);
    }

    /**
     * @throws GuzzleException
     */
    public function chat(array $conversation): array
    {
        return $this->sendRequest('/api/chat', [
            'model' => $this->model,
            'messages' => $conversation,
            'format' => $this->format,
            'options' => $this->options,
            'stream' => $this->stream,
            'tools' => $this->tools,
        ]);
    }

    /**
     * @throws Exception
     */
    public static function processStream(StreamInterface $body, Closure $handleJsonObject): array {
        return self::doProcessStream($body, $handleJsonObject);
    }
}
