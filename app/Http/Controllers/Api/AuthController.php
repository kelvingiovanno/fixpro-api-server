<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

use App\Models\AuthenticationCode;
use App\Models\RefreshToken;

use App\Services\ApiResponseService;



class AuthController extends Controller
{

    private ApiResponseService $apiResponseService;

    public function __construct(ApiResponseService $_apiResponseService)
    {
        $this->apiResponseService = $_apiResponseService;
    }

    public function exchange(Request $request)
    {
    
        $authenticationCode = $request->input('authentication_code');
    
        $authCodeRecord = AuthenticationCode::where('code', $authenticationCode)->first();
    
        if (!$authCodeRecord) {
            return $this->apiResponseService->forbidden('Invalid authentication code');
        }
    
        $customClaims = [
            'sub' => 'profix_api_service',
            'user_id' => $authCodeRecord->user_id,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addDay()->timestamp, 
        ];
    
        $payload = JWTAuth::factory()->customClaims($customClaims)->make();
    
        $accessToken = JWTAuth::encode($payload)->get();
    
        $refreshToken = Str::random(64);
    
        RefreshToken::create([
            'user_id'    => $authCodeRecord->user_id,
            'token'      => $refreshToken,
            'expires_at' => now()->addMonths(3), 
        ]);
    
        $data = [
            "refresh_token" => $refreshToken,
            "access_token"  => $accessToken,
        ];
    
        $authCodeRecord->delete();
    
        return $this->apiResponseService->ok($data, 'ok');
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        $refreshTokenRecord = RefreshToken::where('token', $refreshToken)->first();

        if (!$refreshTokenRecord) {
            return $this->apiResponseService->forbidden('Invalid refresh token');
        }

        if($refreshTokenRecord->expires_at < now()) {
            return $this->apiResponseService->forbidden('expired refresh token');
        }

        $userId = $refreshTokenRecord->user_id;

        $customClaims = [
            'sub'     => 'profix_api_service',
            'user_id' => $userId,
            'iat'     => Carbon::now()->timestamp,
            'exp'     => Carbon::now()->addDay()->timestamp,
        ];

        $payload = JWTAuth::factory()->customClaims($customClaims)->make();

        $accessToken = JWTAuth::encode($payload)->get();

        $data = [
            "access_token" => $accessToken,
        ];

        return $this->apiResponseService->ok($data, 'ok');
    }   
}