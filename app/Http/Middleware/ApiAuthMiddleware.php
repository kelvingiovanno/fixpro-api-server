<?php

namespace App\Http\Middleware;

use App\Services\ApiResponseService;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;


use Closure;
use Exception;


class ApiAuthMiddleware
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
    ) {}

    public function handle($request, Closure $next)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            
            $member_id = $payload->get('member_id');
            $role_id = $payload->get('role_id');
            $capability_ids = $payload->get('capability_ids');

            $request->merge([
                'client' => [
                    'id' => $member_id, 
                    'role_id' => $role_id,
                    'capability_ids' => $capability_ids,
                ],
            ]);
        } 
        catch (TokenExpiredException $e) 
        {
            return $this->apiResponseService->unauthorized('Token has expired', $e->getMessage());
        } 
        catch (TokenInvalidException $e) 
        {
            return $this->apiResponseService->unauthorized('Token is invalid', $e->getMessage());
        } 
        catch (JWTException $e) 
        {
            return $this->apiResponseService->unauthorized('Token is missing', $e->getMessage());
        } 
        catch (Exception $e) 
        {
            return $this->apiResponseService->unauthorized('Unauthorized access', $e->getMessage());
        }

        return $next($request);
    }
}
