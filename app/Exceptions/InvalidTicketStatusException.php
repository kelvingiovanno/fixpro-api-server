<?php

namespace App\Exceptions;

use Exception;

class InvalidTicketStatusException extends Exception
{
    protected $message = 'The ticket status is invalid.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
