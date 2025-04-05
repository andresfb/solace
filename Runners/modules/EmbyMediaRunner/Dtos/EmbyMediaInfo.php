<?php

namespace Modules\EmbyMediaRunner\Dtos;

use Illuminate\Contracts\Support\Arrayable;
use JsonException;

final class EmbyMediaInfo Implements Arrayable
{
    private string $type;

    private string $hash;

    private string $data;

    private int $count;

    /**
     * @throws JsonException
     */
    private function __construct(string $type, string $response)
    {
        $this->type = $type;
        $this->hash = md5($response);
        $this->data = $response;
        $this->count = $this->parseCount($response);
    }

    /**
     * @throws JsonException
     */
    public static function create(string $type, string $response): self
    {
        return new self($type, $response);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'hash' => $this->hash,
            'data' => $this->data,
            'count' => $this->count,
        ];
    }

    /**
     * @throws JsonException
     */
    private function parseCount(string $response): int
    {
        $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $data['TotalRecordCount'];
    }
}
