<?php

declare(strict_types=1);

namespace Modules\ApiConsumers\Services;

use Modules\ApiConsumers\Dtos\OpenAiChatResponse;

use Modules\ApiConsumers\Dtos\OpenAiImageResponse;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAiClient
{
    private string $model;

    private int $maxTokens;

    private float $presencePenalty;

    private string $agentPrompt = '';

    private string $userPrompt = '';

    public function __construct()
    {
        $this->model = config('openai.model');
        $this->maxTokens = config('openai.max_tokens');
        $this->presencePenalty = config('openai.presence_penalty');
    }

    public function setModel(string $model): OpenAiClient
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setMaxTokens(int $maxTokens): OpenAiClient
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function setPresencePenalty(float $presencePenalty): OpenAiClient
    {
        $this->presencePenalty = $presencePenalty;

        return $this;
    }

    public function setAgentPrompt(string $agentPrompt): OpenAiClient
    {
        $this->agentPrompt = $agentPrompt;

        return $this;
    }

    public function getAgentPrompt(): string
    {
        if ($this->userPrompt === '' || $this->userPrompt === '0') {
            throw new \RuntimeException('Agent Prompt not set');
        }

        return $this->agentPrompt;
    }

    public function setUserPrompt(string $userPrompt): OpenAiClient
    {
        $this->userPrompt = $userPrompt;

        return $this;
    }

    public function getUserPrompt(): string
    {
        if ($this->userPrompt === '' || $this->userPrompt === '0') {
            throw new \RuntimeException('User Prompt not set');
        }

        return $this->userPrompt;
    }

    public function ask(): OpenAiChatResponse
    {
        $response = OpenAI::chat()
            ->create(
                $this->prepareOptions()
            );

        return OpenAiChatResponse::fromResponse($response);
    }

    public function image(): OpenAiImageResponse
    {
        $response = OpenAI::images()
            ->create(
                $this->prepareImageOptions()
            );

        return OpenAiImageResponse::fromResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareOptions(): array
    {
        return [
            'model' => $this->getModel(),
            'max_tokens' => $this->maxTokens,
            'presence_penalty' => $this->presencePenalty,
            'messages' => [[
                'role' => 'system',
                'content' => $this->getAgentPrompt(),
            ], [
                'role' => 'user',
                'content' => $this->getUserPrompt(),
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareImageOptions(): array
    {
        return [
            'model' => $this->getModel(),
            'prompt' => $this->getUserPrompt(),
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ];
    }
}
