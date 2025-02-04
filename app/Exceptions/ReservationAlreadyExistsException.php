<?php

namespace App\Exceptions;

use Exception;

class ReservationAlreadyExistsException extends Exception
{
    public function __construct(string $message = "Reservation already exists for this event. Please update your existing reservation.", int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
