<?php

namespace App\Services;

class EncryptionService
{
    private string $CIPHER = 'AES-256-CBC';

    public function encrypt(string $plaintext, string $key): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->CIPHER));
        $encrypted = openssl_encrypt($plaintext, $this->CIPHER, $key, 0, $iv);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $ciphertext, string $key): string
    {
        $data = base64_decode($ciphertext);
        $ivLength = openssl_cipher_iv_length($this->CIPHER);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $decrypted = openssl_decrypt($encrypted, $this->CIPHER, $key, 0, $iv);

        if ($decrypted === false) {
            throw new \Exception('Decryption failed');
        }

        return $decrypted;
    }
}
