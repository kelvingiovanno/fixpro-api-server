<?php

namespace App\Services;

use App\Exceptions\InvalidTokenException;

use App\Models\AuthenticationCode;
use App\Models\RefreshToken;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthService
{
    public function exchange_code(string $code): array
    {
        $authenticationCode = AuthenticationCode::findOrFail($code);
        $now = now();

        $accessExpiry = $now->copy()->addMinutes(5);
        $refreshExpiry = $now->copy()->addMonths(3);

        $member = $authenticationCode->applicant->member;

        $customClaims = [
            'sub' => 'profix_api_service',
            'member_id' => $member->id,
            'role_id' => $member->role->id,
            'capability_ids' => $member->capabilities->pluck('id')->toArray(), 
            'iat' => $now->timestamp,
            'exp' => $accessExpiry->timestamp,
        ];

        $accessToken = JWTAuth::encode(
            JWTAuth::factory()->customClaims($customClaims)->make()
        )->get();

        
        $member->update([
            'access_token' => $accessToken,
        ]);

        $refreshToken = Str::random(100);

        RefreshToken::create([
            'member_id' => $member->id,
            'token' => $refreshToken,
            'expires_on' => $refreshExpiry,
        ]);

        $authenticationCode->delete();

        return [
            'access_token' => $accessToken,
            'access_expiry_interval' => $now->diffInSeconds($accessExpiry),
            'refresh_token' => $refreshToken,
            'refresh_expiry_interval' => $now->diffInSeconds($refreshExpiry),
            'token_type' => 'Bearer',
            'role_scope' => $member->role->name,
            'capabilities' => $member->capabilities->map(function ($capability){
                return $capability->name;
            }),
            'specialties' => $member->specialities->map(function ($specialty){
                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                    'service_level_agreement_duration_hour' => (string) $specialty->sla_hours,
                ];
            }),
        ];
    }

    public function refresh_token(string $tokenValue): array
    {
        $token = RefreshToken::where('token', $tokenValue)->firstOrFail();

        if ($token->expires_on < now()) {
            throw new InvalidTokenException('Refresh token expired.');
        }

        $token->delete();

        $now = now();
        $accessExpiry = $now->copy()->addDay();
        $refreshExpiry = $now->copy()->addMonths(3);

        $newToken = RefreshToken::create([
            'member_id' => $token->member->id,
            'token' => Str::uuid(100),
            'expires_on' => $refreshExpiry,
        ]);

        $customClaims = [
            'sub' => 'profix_api_service',
            'member_id' => $newToken->member->id,
            'role_id' => $newToken->member->role->id,
            'capability_ids' => $newToken->member->capabilities->pluck('id')->toArray(),
            'iat' => $now->timestamp,
            'exp' => $accessExpiry->timestamp,
        ];

        $accessToken = JWTAuth::encode(
            JWTAuth::factory()->customClaims($customClaims)->make()
        )->get();

        $token->member->update([    
            'access_token' => $accessToken,
        ]);

        return [
            'access_token' => $accessToken,
            'access_expiry_interval' => $accessExpiry->diffInMilliseconds($now),
            'refresh_token' => $newToken->token,
            'refresh_expiry_interval' => $refreshExpiry->diffInMilliseconds($now),
            'token_type' => 'Bearer',
            'role_scope' => $newToken->member->role->name,
            'capabilities' => $newToken->member->capabilities->map(function ($capability){
                return $capability->name;
            }),
            'specialties' => $newToken->member->specialities->map(function ($specialty){
                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                    'service_level_agreement_duration_hour' => (string) $specialty->sla_hours,
                ];
            }),
        ];
    }
}
