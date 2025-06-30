<?php

namespace App\Http\Middleware;

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;

use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Closure;

class CapabilityMiddleware
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
    ) {}

    public function handle(Request $request, Closure $next, string $capability): Response
    {
        $client_role_id = $request->client['role_id'];

        if($client_role_id == MemberRoleEnum::MANAGEMENT->id() || $client_role_id == MemberRoleEnum::MEMBER->id())
        {
            return $next($request);
        }

        if($client_role_id == MemberRoleEnum::CREW->id())
        {
            $client_capability_ids = $request->client['capability_ids'];

            $required_capability_id = MemberCapabilityEnum::from($capability)->id();

            if (!in_array($required_capability_id, $client_capability_ids)) {
                return $this->apiResponseService->forbidden('You are not authorized to access this resource.');
            }

            return $next($request); 
        }

        return $this->apiResponseService->forbidden('You are not authorized to access this resource.');
    }

}
