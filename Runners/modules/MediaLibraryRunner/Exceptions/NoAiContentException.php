<?php

namespace Modules\MediaLibraryRunner\Exceptions;

use Exception;

class NoAiContentException extends Exception
{
    public function __construct(string $message, public readonly array $response)
    {
        parent::__construct($message);
    }
}
