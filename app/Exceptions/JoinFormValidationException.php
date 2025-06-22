<?php

namespace App\Exceptions;

use Exception;

class JoinFormValidationException extends Exception
{
    protected $message = 'Invalid join form data.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
