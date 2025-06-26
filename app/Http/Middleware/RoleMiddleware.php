<?php

namespace App\Http\Middleware;

use App\Enums\MemberRoleEnum;

use App\Services\ApiResponseService;

use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;

use Closure;

class RoleAuthMiddleware
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
    ) {}

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        
        $allowed_role_ids = collect($roles)
            ->map(fn ($role) => MemberRoleEnum::idFromName($role))
            ->all();

        if (!$request->client['role_id'] || !in_array($request->client['role_id'], $allowed_role_ids)) {
            
            return $this->apiResponseService->forbidden('You are not authorized to access this resource.');   
        }

        return $next($request); 
    }
}