<?php

namespace App\Services\Id;

use Illuminate\Support\Str;

abstract class BasedIdServices
{
    protected function generateUuid(): string
    {
        return Str::uuid();
    }

    public abstract function checkId(string $_id) : bool ;
}