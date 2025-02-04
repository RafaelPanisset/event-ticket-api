<?php
namespace App\Exceptions;

use Exception;

class EventDeletionException extends Exception
{
    protected $code = 422;

    public function __construct($message = 'Cannot delete event with existing reservations', int $code = 422)
    {
        parent::__construct($message);
    }
}
