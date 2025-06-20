<?php

namespace App\Http\Controllers\Api;

use App\Models\AuthenticationCode;
use App\Models\RefreshToken;

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
        $authenticationCode = $_request->input('data.authentication_code');
        
        if (!$authenticationCode) {
            return $this->apiResponseService->forbidden('Authentication code is required');
        }

        if (!Str::isUuid($authenticationCode)) {
            return $this->apiResponseService->forbidden('Invalid authentication code'); 
        }

        try 
        {
            $authCodeRecord = AuthenticationCode::find($authenticationCode);

            if (!$authCodeRecord) {
                return $this->apiResponseService->forbidden('Invalid authentication code');
            }
        
            $now = Carbon::now();
            $accessExpiry = $now->copy()->addDay();
            $refreshExpiry = $now->copy()->addMonths(3);
            
        
            $customClaims = [
                'sub' => 'profix_api_service',
                'member_id' => $authCodeRecord->applicant->member->id,
                'member_role_id' => $authCodeRecord->applicant->member->role->id,
                'iat' => $now->timestamp,
                'exp' => $accessExpiry->timestamp,
            ];
        
            $payload = JWTAuth::factory()->customClaims($customClaims)->make();
            $accessToken = JWTAuth::encode($payload)->get();
        
            $refreshToken = Str::random(302);
 
            RefreshToken::create([
                'member_id'    => $authCodeRecord->applicant->member->id,
                'token'      => $refreshToken,
                'expires_on' => $refreshExpiry,
            ]);
        
            $response_data = [
                "access_token"  => $accessToken,
                "access_expiry_interval" => $accessExpiry->diffInMilliseconds($now),
                "refresh_token" => $refreshToken,
                "refresh_expiry_interval" => $refreshExpiry->diffInMilliseconds($now),
                "token_type" => "Bearer",
                "role_scope" => $authCodeRecord->applicant->member->role->name,
            ];
    
            $authCodeRecord->delete();
    
            return $this->apiResponseService->ok($response_data, 'Authentication successful');
            
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
            $refreshToken = $_request->input('data.refresh_token');
    
            if (!$refreshToken) {
                return $this->apiResponseService->forbidden('Refresh token is required');
            }
    
            $refreshTokenRecord = RefreshToken::where('token', $refreshToken)->first();
    
            if (!$refreshTokenRecord) {
                return $this->apiResponseService->forbidden('Invalid refresh token');
            }
    
            if ($refreshTokenRecord->expires_on < now()) {
                return $this->apiResponseService->forbidden('Expired refresh token');
            }

            $refreshTokenRecord->delete();

            $now = Carbon::now();
            $accessExpiry = $now->copy()->addDay();
            $refreshExpiry = $now->copy()->addMonths(3);

            $new_refresh_token = RefreshToken::create([
                'member_id'    => $refreshTokenRecord->member->id,
                'token'      => Str::random(302),
                'expires_at' => $refreshExpiry,
            ]);
    
            $customClaims = [
                'sub' => 'profix_api_service',
                'member_id' => $new_refresh_token->member->id,
                'member_role_id' => $new_refresh_token->member->role->id,
                'iat' => $now->timestamp,
                'exp' => $accessExpiry->timestamp,
            ];
    
            $payload = JWTAuth::factory()->customClaims($customClaims)->make();
            $accessToken = JWTAuth::encode($payload)->get();
    
            $response_data = [
                "access_token"  => $accessToken,
                "access_expiry_interval" => $accessExpiry->diffInMilliseconds($now),
                "refresh_token" => $new_refresh_token->token,
                "refresh_expiry_interval" => $refreshExpiry->diffInMilliseconds($now),
                "token_type" => "Bearer",
                "role_scope" => $new_refresh_token->member->role->name,
            ];
    
            return $this->apiResponseService->ok($response_data, 'Access token successfully refreshed');
            
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