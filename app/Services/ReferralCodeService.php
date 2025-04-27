<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ReferralCodeService
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
}   