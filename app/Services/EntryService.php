<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

use App\Models\Applicant;
use App\Models\AuthenticationCode;
use Illuminate\Http\Request;

class EntryService
{
    public function generateReferral() : string
    {
        $new_referral = Str::random(5);

        if(Cache::has('referral'))
        {
            Cache::forget('referral');
        }

        Cache::forever('referral', $new_referral);

        return $new_referral;
    }

    public function getReferral() : string
    {

        if(Cache::has('referral'))
        {
            return Cache::get('referral', '');    
        }

        return $this->generateReferral();
    }

    public function deleteReferral() : void
    {
        Cache::forget('referral');
    }


    public function checkReferral($_referral) : bool
    {
        return Cache::get('referral') === $_referral;
    }

    public function generateNonce() : string
    {
        do {
            $nonce = Str::random(64); 
        } while (Cache::has("nonce:$nonce")); 

        Cache::store('redis')->put("nonce:$nonce", true, now()->addHour());

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

    public function checkApplicationId($_applicationId): bool 
    {
        if (!Str::isUuid($_applicationId)) {
            return false; 
        }

        return Applicant::where('id', $_applicationId)->exists();
    }

    public function isApplicationAccepted(string $_applicationId): bool
    {

        if (!Str::isUuid($_applicationId)) {
            return false;
        }

        $applicant = Applicant::where('id', $_applicationId)
            ->where('is_accepted', true)
            ->first();

        if (!$applicant) {
            return false;
        }
        
        return true;
    }
}   