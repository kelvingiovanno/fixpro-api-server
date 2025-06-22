<?php

namespace App\Services;

use App\Exceptions\InvalidReferralException;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ReferralCodeService
{
    public function generate() : string
    {
        $new_referral = Str::random(5);

        if(Cache::has('referral'))
        {
            Cache::forget('referral');
        }

        Cache::forever('referral', $new_referral);

        return $new_referral;
    }

    public function get() : string
    {

        if(Cache::has('referral'))
        {
            return Cache::get('referral', '');    
        }

        return $this->generate();
    }

    public function delete() : void
    {
        Cache::forget('referral');
    }


    public function check($_referral) : void
    {
        $stored = Cache::get('referral');

        if ($stored === null) {
            throw new InvalidReferralException('No referral code has been generated.');
        }

        if ($stored !== $_referral) {
            throw new InvalidReferralException();
        }
    }
}   