<?php

namespace App\Exceptions;

use Exception;

class JoinAreaException extends Exception
{
    protected $message = 'Join policy error.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
