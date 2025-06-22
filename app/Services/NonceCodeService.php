<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Exceptions\InvalidNonceException;

class NonceCodeService 
{
    public function generate(): string
    {
        do {
            $nonce = Str::random(64); 
        } while (Cache::has("nonce:$nonce")); 

        Cache::put("nonce:$nonce", true, now()->addMinutes(15));

        return $nonce;
    }

    /**
     * @throws InvalidNonceException
     */
    public function check(string $nonce): void
    {
        if (!Cache::has("nonce:$nonce")) {
            throw new InvalidNonceException();
        }
    }

    /**
     * @throws InvalidNonceException
     */
    public function delete(string $nonce): void
    {
        if (!Cache::has("nonce:$nonce")) {
            throw new InvalidNonceException('Cannot delete nonce because it is invalid or already deleted.');
        }

        Cache::forget("nonce:$nonce");
    }
}
