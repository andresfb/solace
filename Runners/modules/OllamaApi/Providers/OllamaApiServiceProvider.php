<?php

declare(strict_types=1);

namespace Modules\OllamaApi\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\OllamaApi\Services\Ollama;

/**
 * This Module based on the code from the cloudstudio/ollama-laravel package
 * https://github.com/cloudstudio/ollama-laravel
 *
 * Simplified to ignore the ModelService and modified to be able to use more
 * than one Ollama server (URL)
 */
class OllamaApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Ollama::class, fn($app): Ollama => new Ollama());
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'ollama-laravel');
    }
}
