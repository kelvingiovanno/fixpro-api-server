<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NonceCodeService 
{
    public function generateNonce() : string
    {
        do {
            $nonce = Str::random(64); 
        } while (Cache::has("nonce:$nonce")); 

        Cache::put("nonce:$nonce", true, now()->addMinutes(15));

        return $nonce;
    }

    public function checkNonce(string $nonce) : bool
    {
        if (Cache::has("nonce:$nonce")) {
            return true;
        }

        return false;
    }

    public function deleteNonce(string $nonce) : bool
    {
        if (Cache::has("nonce:$nonce")) {
            Cache::forget("nonce:$nonce");
            return true;
        }

        return false;
    }
}