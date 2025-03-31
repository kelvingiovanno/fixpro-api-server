<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\ApiResponseService;

class ApiAuthMiddleware
{
    protected $apiResponse;

    public function __construct(ApiResponseService $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function handle($request, Closure $next)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $request->merge(['jwt_payload' => $payload]);
        } catch (TokenExpiredException $e) {
            return $this->apiResponse->error('Token has expired', 401, $e->getMessage());
        } catch (TokenInvalidException $e) {
            return $this->apiResponse->error('Token is invalid', 401, $e->getMessage());
        } catch (JWTException $e) {
            return $this->apiResponse->error('Token is missing', 401, $e->getMessage());
        } catch (Exception $e) {
            return $this->apiResponse->error('Unauthorized access', 401, $e->getMessage());
        }

        return $next($request);
    }
}
