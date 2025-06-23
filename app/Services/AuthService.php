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

        $accessExpiry = $now->copy()->addDay();
        $refreshExpiry = $now->copy()->addMonths(3);

        $member = $authenticationCode->applicant->member;

        $customClaims = [
            'sub' => 'profix_api_service',
            'member_id' => $member->id,
            'role_id' => $member->role->id,
            'iat' => $now->timestamp,
            'exp' => $accessExpiry->timestamp,
        ];

        $accessToken = JWTAuth::encode(
            JWTAuth::factory()->customClaims($customClaims)->make()
        )->get();

        $refreshToken = Str::random(302);

        RefreshToken::create([
            'member_id' => $member->id,
            'token' => $refreshToken,
            'expires_on' => $refreshExpiry,
        ]);

        $authenticationCode->delete();

        return [
            'access_token' => $accessToken,
            'access_expiry_interval' => $accessExpiry->diffInMilliseconds($now),
            'refresh_token' => $refreshToken,
            'refresh_expiry_interval' => $refreshExpiry->diffInMilliseconds($now),
            'token_type' => 'Bearer',
            'role_scope' => $member->role->name,
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
            'token' => Str::uuid(),
            'expires_on' => $refreshExpiry,
        ]);

        $customClaims = [
            'sub' => 'profix_api_service',
            'member_id' => $newToken->member->id,
            'role_id' => $newToken->member->role->id,
            'iat' => $now->timestamp,
            'exp' => $accessExpiry->timestamp,
        ];

        $accessToken = JWTAuth::encode(
            JWTAuth::factory()->customClaims($customClaims)->make()
        )->get();

        return [
            'access_token' => $accessToken,
            'access_expiry_interval' => $accessExpiry->diffInMilliseconds($now),
            'refresh_token' => $newToken->token,
            'refresh_expiry_interval' => $refreshExpiry->diffInMilliseconds($now),
            'token_type' => 'Bearer',
            'role_scope' => $newToken->member->role->name,
        ];
    }
}
