<?php

declare(strict_types=1);

namespace App\Libraries\Cyphers;

use App\Models\Cyphers\Cypher;

abstract readonly class BaseCypherLibrary
{
    private Cypher $model;

    public function __construct()
    {
        $this->model = Cypher::where('name', $this->getIdentifier())
            ->firstOrFail();
    }

    abstract protected function getIdentifier(): string;

    public function getId(): int
    {
        return $this->model->id;
    }

    public function getName(): string
    {
        return $this->model->name;
    }

    public function getClue(): string
    {
        return $this->model->clue;
    }
}
