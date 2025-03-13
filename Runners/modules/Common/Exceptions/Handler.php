<?php

declare(strict_types=1);

namespace Modules\Common\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Modules\Common\Events\UpdateModelSettingsEvent;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof EmptyRunException) {
            Log::info($e->getMessage());

            UpdateModelSettingsEvent::dispatch($e->modelSettings);
        }

        return parent::render($request, $e);
    }
}
