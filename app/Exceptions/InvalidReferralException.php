<?php

namespace App\Exceptions;

use Exception;

class InvalidReferralException extends Exception
{
    protected $message = 'The provided referral code is invalid.';

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message);
    }
}
