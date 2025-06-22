<?php
namespace App\Exceptions;

use Exception;

class InvalidTokenException extends Exception
{
    protected $message = 'The token is invalid.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
