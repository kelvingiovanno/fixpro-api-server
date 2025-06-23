<?php

namespace App\Exceptions;

use Exception;

class GoogleCalenderException extends Exception
{
    protected $message = 'Google Calendar integration error.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
