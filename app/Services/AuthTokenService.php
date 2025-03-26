<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthTokenService 
{
    
    private const CACHE_KEY = 'web_auth_token';

    /**
     * Generate and store the token only once per server session.
     */
    public static function generateAndStoreKey(): string
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Str::random(32);
        });
    }

    /**
     * Validate the provided token against the stored token.
     */
    public static function checkValidToken(string $_token): bool
    {
        return $_token === Cache::get(self::CACHE_KEY);
    }
    
}