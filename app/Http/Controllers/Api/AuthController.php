<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Exceptions\InvalidTokenException;

use App\Services\ApiResponseService;
use App\Services\AuthService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Throwable;

class AuthController extends Controller
{
    public function __construct (
        protected ApiResponseService $apiResponseService,
        protected AuthService $authService,
    ) {}

    public function exchange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.authentication_code' => 'required|string|uuid|exists:authentication_codes,id',
        ],
        [
            'data.required' => 'The request must include a data object.',
            'data.array' => 'The data field must be a valid object.',
            
            'data.authentication_code.required' => 'Authentication code is required.',
            'data.authentication_code.string' => 'Authentication code must be a string.',
            'data.authentication_code.uuid' => 'Authentication code format is invalid.',
            'data.authentication_code.exists' => 'The authentication code provided is invalid',
        ]);
        

        if ($validator->fails()) 
        {
            return $this->apiResponseService->badRequest('Validation failed.' ,$validator->errors());
        }

        try 
        {
            $resposne_data = $this->authService->exchange_code($request['data']['authentication_code']);
            return $this->apiResponseService->ok($resposne_data, 'Authentication code exchanged successfully.');   
        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('The authentication code provided is invalid.');
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while exchanging the authentication code.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(), 
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
    
    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.refresh_token' => 'required|string|exists:refresh_tokens,token'
        ], 
        [
            'data.required' => 'The request must include a data object.',
            'data.array' => 'The data field must be a valid object.',
            
            'data.refresh_token.required' => 'The refresh token is required.',
            'data.refresh_token.string' => 'The refresh token must be a string.',
            'data.refresh_token.exists' => 'The refresh token is invalid or has expired.',
        ]);


        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try 
        {
            $resposne_data = $this->authService->refresh_token($request['data']['refresh_token']);
            return $this->apiResponseService->ok($resposne_data, 'Access token successfully refreshed.');
        }
        catch (InvalidTokenException $e) 
        {
            return $this->apiResponseService->forbidden($e->getMessage());
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occurred while refreshing the access token.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}