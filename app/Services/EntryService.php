<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

use App\Models\PendingApplication;
use App\Models\AuthenticationCode;
use App\Models\RefreshToken;
use App\Models\User;
use App\Models\UserData;
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

    public function checkApplicationId($_applicationId): bool 
    {
        return PendingApplication::where('application_id', $_applicationId)->exists();
    }

    public function isApplicationAccepted(Request $request, string $applicationId): bool
    {
        $pendingApplication = PendingApplication::where('application_id', $applicationId)
            ->where('is_accepted', true)
            ->first();

        if (!$pendingApplication) {
            return false;
        }

        $user = User::create([]);

        $applicationData = $pendingApplication->toArray();
        unset($applicationData['application_id'], $applicationData['is_accepted']);

        $applicationData['user_id'] = $user->id;

        UserData::create($applicationData);

        $pendingApplication->delete();

        $request->merge(['user_id' => $user->id]);

        return true;
    }



    public function generateAuthenticationCode($_userId): string
    {
        $newAuthenticationCode = 'AUTH-' . Str::uuid();

        AuthenticationCode::create([
            'code' => $newAuthenticationCode,
            'user_id' => $_userId,
            'expires_at' => now()->addMinutes(10), 
        ]);


        return $newAuthenticationCode;
    }

}   