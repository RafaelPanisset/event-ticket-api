<?php

namespace App\Exceptions;

use Exception;

class PastEventReservationException extends Exception
{
    public function __construct(string $message = "Cannot reserve or update tickets for past events.", int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
