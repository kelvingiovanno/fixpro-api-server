<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NonceService
{
 
    public function generateNonce(): string
    {
        do {
            $nonce = Str::random(64); 
        } while (Cache::store('redis')->has("nonce:$nonce")); 

        Cache::store('redis')->put("nonce:$nonce", true, 60);

        return $nonce;
    }

    public function checkNonce(string $nonce): bool
    {
        if (Cache::store('redis')->has("nonce:$nonce")) {
            Cache::store('redis')->forget("nonce:$nonce");
            return true;
        }

        return false;
    }
}
