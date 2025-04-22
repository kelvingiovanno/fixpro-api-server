<?php

namespace App\Http\Controllers\Api;

use App\Models\AuthenticationCode;
use App\Models\RefreshToken;
use App\Models\Applicant;

use App\Services\ApiResponseService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Throwable;

class AuthController extends Controller
{
    private ApiResponseService $apiResponseService;

    public function __construct (
        ApiResponseService $_apiResponseService
    ) {
        $this->apiResponseService = $_apiResponseService;
    }

    public function exchange(Request $_request)
    {
        try 
        {
            $authenticationCode = $_request->input('authentication_code');
    
            if (!$authenticationCode) {
                return $this->apiResponseService->forbidden('Authentication code is required');
            }
    
            $authCodeRecord = AuthenticationCode::find($authenticationCode);
    
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
            Applicant::find($authCodeRecord->applicant_id)->delete();
    
            return $this->apiResponseService->ok($data, 'Authentication successful');
            
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred during authentication exchange', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(), 
            ]);
    
            return $this->apiResponseService->internalServerError('Failed to exchange authentication code', 500);
        }
    }
    
    public function refresh(Request $_request)
    {
        try 
        {
            $refreshToken = $_request->input('refresh_token');
    
            if (!$refreshToken) {
                return $this->apiResponseService->forbidden('Refresh token is required');
            }
    
            $refreshTokenRecord = RefreshToken::where('token', $refreshToken)->first();
    
            if (!$refreshTokenRecord) {
                return $this->apiResponseService->forbidden('Invalid refresh token');
            }
    
            if ($refreshTokenRecord->expires_at < now()) {
                return $this->apiResponseService->forbidden('Expired refresh token');
            }
    
            $userId = $refreshTokenRecord->user_id;
            $customClaims = [
                'sub' => 'profix_api_service',
                'user_id' => $userId,
                'iat' => Carbon::now()->timestamp,
                'exp' => Carbon::now()->addDay()->timestamp,
            ];
    
            $payload = JWTAuth::factory()->customClaims($customClaims)->make();
            $accessToken = JWTAuth::encode($payload)->get();
    
            $data = [
                "access_token" => $accessToken,
            ];
    
            return $this->apiResponseService->ok($data, 'Access token successfully refreshed');
            
        } 
        catch (Throwable $e) 
        {
            Log::error('Error occurred during token refresh', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Failed to refresh token', 500);
        }
    }
}