<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Services\EntryService;
use App\Services\ApiResponseService;

use App\Models\User;
use App\Models\UserData;

class EntryMiddleware
{
    private EntryService $entryService;
    private ApiResponseService $apiResponseService;

    public function __construct(EntryService $entryService, ApiResponseService $apiResponseService)
    {
        $this->entryService = $entryService;
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $referral = $request->query('ref');
        $nonce = $request->query('nonce');
        $application_id = $request->query('application_id');

        $paramCount = ($referral ? 1 : 0) + ($nonce ? 1 : 0) + ($application_id ? 1 : 0);

        if ($paramCount === 0) {
            return $this->apiResponseService->badRequest('Require params');
        }

        if ($paramCount > 1) {
            return $this->apiResponseService->badRequest('Require params');
        }

        if ($referral && !$this->entryService->checkReferral($referral)) {
            return $this->apiResponseService->unprocessableEntity('Invalid referral');
        }

        if ($nonce && !$this->entryService->checkNonce($nonce)) {
            return $this->apiResponseService->unprocessableEntity('Invalid nonce');
        }

        if ($application_id) {

            if (!$this->entryService->checkApplicationId($application_id)) {
                return $this->apiResponseService->unprocessableEntity('Invalid application ID');
            }

            if (!$this->entryService->isApplicationAccepted($request, $application_id)) {
                return $this->apiResponseService->forbidden('The application has not been accepted');
            }
        }

        return $next($request);
    }
}
