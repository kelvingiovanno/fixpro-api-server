<?php 

namespace App\Services;

use App\Models\ReferralCode;

class ReferralCodeService
{
    public function generateCode(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $code = $this->generateRandomString(6, $characters);
        } while ($this->isCodeExists($code));

        return $code;
    }

    public function isCodeValid(string $_code): bool
    {
        if ($this->isCodeExists($_code)) 
        {
            ReferralCode::where('code', $_code)->update(['last_active_at' => now()]);
            return true;
        }

        return false;
    }

    public function deleteReferralCode(): bool 
    {
        $deleted = ReferralCode::orderBy('created_at', 'desc')->first()?->delete();

        return $deleted > 0;
    }


    private function generateRandomString(int $length, string $characters): string
    {
        $randomString = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $maxIndex)];
        }

        return $randomString;
    }

    private function isCodeExists(string $_code): bool
    {
        return ReferralCode::where('code', $_code)->exists();
    }
}
