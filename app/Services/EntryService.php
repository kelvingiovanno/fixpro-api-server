<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

use App\Models\PendingApplication;
use App\Models\AuthenticationCode;
use App\Models\RefreshToken;


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

    public function checkApplicationId($_id): bool 
    {
        return PendingApplication::where('application_id', $_id)->exists();
    }

    public function isApplicationAccepted(string $_id): bool
    {
        return PendingApplication::where('application_id', $_id)->where('is_accepted', true)->exists();
    }

    public function generateAuthenticationCode(): string
    {
        $newAuthenticationCode = 'AUTH-' . Str::uuid();

        AuthenticationCode::create([
            'code' => $newAuthenticationCode,
            'expires_at' => now()->addMinutes(10), 
        ]);


        return $newAuthenticationCode;
    }
    
    public function exchangeToken($_authentication_code, $_userId) : array
    {

        if(!AuthenticationCode::where('code', $_authentication_code)->exists())
        {   
            return [];
        }

        $customClaims = [
            'sub' => 'profix_api_service', 
            'iat' => Carbon::now()->timestamp, 
            'exp' => Carbon::now()->day(1)->timestamp, 
        ];

        $payload = JWTAuth::factory()->customClaims($customClaims)->make();

        $access_token = JWTAuth::encode($payload)->get();
        $refresh_token = Str::random(64);
        
        
        RefreshToken::create([
            'user_id' => $_userId,
            'token' => $refresh_token,
            'expires_at' => now()->months(3),
        ]);

        return [
            "refresh_token" => $refresh_token,
            "access_token" => $access_token,
        ];
    }
}   