<?php

namespace App\Exceptions;

use Exception;

class InvalidStorageTypeException extends Exception
{
    protected $message = 'The specified storage type is not supported.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
