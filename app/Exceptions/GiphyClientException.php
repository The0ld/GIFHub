<?php

namespace App\Exceptions;

use Exception;

class GiphyClientException extends Exception
{
    protected $statusCode;

    public function __construct(string $message, int $statusCode)
    {
        if ($message === 'Validation error') {
            $statusCode = 422;
        }
        $this->statusCode = $statusCode;
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
