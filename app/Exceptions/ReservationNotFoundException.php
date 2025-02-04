<?php

namespace App\Exceptions;

use Exception;

class ReservationNotFoundException extends Exception
{

    public function __construct(string $message = "Reservation not found.", int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
