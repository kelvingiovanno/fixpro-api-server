<?php

namespace App\Http\Middleware;

use App\Services\ApiResponseService;
use App\Services\NonceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NonceMiddleware
{
    private ApiResponseService $apiResponseService;
    private NonceService $nonceService;

    public function __construct(ApiResponseService $_apiResponseService, NonceService $_nonceService)
    {
        $this->apiResponseService = $_apiResponseService;
        $this->nonceService = $_nonceService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $nonceToken = $request->query('nonce');
    
        if (!$nonceToken) {
            return $this->apiResponseService->error(false, 400, "Nonce token is required");
        }
    
        if (!$this->nonceService->checkNonce($nonceToken)) {
            return $this->apiResponseService->error(false, 403, "Invalid nonce token");
        }
    
        return $next($request);
    }
    
}
