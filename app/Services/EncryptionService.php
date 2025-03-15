<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Models\ReferralCode;

class EncryptionService
{
    /**
     * Generate a secure encryption key.
     */
    public function generateKey(): string
    {
        
        do {
            $key = base64_encode(Str::random(32));
        } while ($this->isKeyExists($key));

        return base64_encode(Str::random(32));
    }

    /**
     * Encrypt data.
     */
    public function encrypt($data): string
    {
        return Crypt::encryptString($data);
    }

    /**
     * Decrypt data.
     */
    public function decrypt($encryptedData): string
    {
        return Crypt::decryptString($encryptedData);
    }

    private function isKeyExists(string $_key) {
        return ReferralCode::where('key', $_key)->exists();
    }
}
