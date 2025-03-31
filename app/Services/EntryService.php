<?php

namespace App\Services;


use App\Models\PendingApplication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


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

    public function checkReferral($_referral) : bool
    {
        return Cache::get('referral') === $_referral;
    }

    public function generateNonce() : string
    {
        do {
            $nonce = Str::random(64); 
        } while (Cache::has("nonce:$nonce")); 

        Cache::store('redis')->put("nonce:$nonce", true, now()->addMinute());

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

    public function generateApplicationId() : string
    {
        return 'APP-' . Str::uuid();
    }

    public function checkId($_id): bool 
    {
        return PendingApplication::where('application_id', $_id)->exists();
    }

    // public function generateAuntenticationCode() : string
    // {

    // }

    // public function checkAuthenticationCode() : bool 
    // {
        
    // }
}