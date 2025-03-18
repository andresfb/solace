<?php

namespace Modules\OllamaApi\Facades;

use Illuminate\Support\Facades\Facade;

class Ollama extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Modules\OllamaApi\Services\Ollama::class;
    }
}
