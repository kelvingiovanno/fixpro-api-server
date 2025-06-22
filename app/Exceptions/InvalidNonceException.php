<?php

namespace App\Exceptions;

use Exception;

class InvalidNonceException extends Exception
{
    protected $message = 'The provided Nonce code is invalid.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
