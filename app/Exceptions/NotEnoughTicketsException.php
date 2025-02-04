<?php

namespace App\Exceptions;

use Exception;

class NotEnoughTicketsException extends Exception
{
    public function __construct(string $message = "Not enough tickets available.", int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
