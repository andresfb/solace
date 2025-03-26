<?php

namespace Modules\MediaLibraryRunner\Exceptions;

use Exception;

class NoAiContentException extends Exception
{
    /**
     * @param  array<string, mixed>|List<string>  $response
     */
    public function __construct(string $message, public readonly array $response)
    {
        parent::__construct($message);
    }
}
