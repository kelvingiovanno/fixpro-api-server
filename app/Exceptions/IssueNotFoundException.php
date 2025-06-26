<?php

namespace App\Exceptions;

use Exception;

class IssueNotFoundException extends Exception
{
    protected $message = 'Issue not found.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
