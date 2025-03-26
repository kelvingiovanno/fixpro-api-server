<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NonceService
{
    /**
     * Generate a unique nonce token and store it in Redis for 1 minute.
     *
     * @return string The generated nonce token.
     */
    public function generateNonce(): string
    {
        do {
            $nonce = Str::random(32); 
        } while (Cache::store('redis')->has("nonce:$nonce")); 

        Cache::store('redis')->put("nonce:$nonce", true, 60);

        return $nonce;
    }

    /**
     * Check if the nonce exists in Redis (and delete it if found).
     *
     * @param string $nonce
     * @return bool
     */
    public function checkNonce(string $nonce): bool
    {
        if (Cache::store('redis')->has("nonce:$nonce")) {
            Cache::store('redis')->forget("nonce:$nonce");
            return true;
        }

        return false;
    }
}
