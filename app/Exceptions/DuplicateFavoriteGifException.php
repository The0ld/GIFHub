<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DuplicateFavoriteGifException extends Exception
{
    public function __construct($message = "There is already a favorite GIF with this ID for this user.", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
